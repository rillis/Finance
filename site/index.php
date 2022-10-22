<?php
  require "secret.php";

   $conn = new mysqli($servername, $username, $password, $dbname);

   if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  function datefromsql($sqldate){
    return DateTime::createFromFormat('Y-m-d', $sqldate);
  }

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
      .start{
        margin-top: 2vh;
      }
      .test1{
        background-color: red;
      }
      .finish{
        margin-bottom: 2vh;
      }
      .link{
        text-decoration:none;
      }
      .paid{
        text-decoration-line: line-through;
      }
    </style>
  </head>
  <body>
    <div class="container start">
      <form class="row" method="get">
        <div class="col-2">
          <select class="form-select" aria-label="Anomes Choose" id="anomes_drop" name="anomes">
            <?php
            $sql = "SELECT anomes FROM bills group by anomes";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
              // output data of each row
              while($row = $result->fetch_assoc()) {
                echo '<option value="'.$row["anomes"].'">'.$row["anomes"].'</option>';
              }
            }
            ?>
          </select>
        </div>
        <div class="col-3">
          <button type="submit" class="btn btn-primary mb-3">Confirm</button>
          <button type="button" class="btn btn-success mb-3">+ Anomes</button>
          <?php if(isset($_GET["anomes"])){ ?>
            <button type="button" data-bs-toggle="modal" data-bs-target="#modalEdit" data-action="add" class="btn btn-success mb-3">+ Bill</button>
          <?php } ?>
        </div>
      </form>
    </div>
    <?php
      if(isset($_GET["anomes"])){

        $sql = "SELECT * FROM bills where anomes=".$_GET["anomes"];
        $result = $conn->query($sql);
        ?>
        <div class="container start">
          <table class="table" id="dataTable">
            <thead>
              <tr>
                <th scope="col">Sum</th>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Value</th>
                <th scope="col">Payday</th>
                <th scope="col">Limit Day</th>
                <th scope="col">Status</th>
                <th scope="col">Options</th>
              </tr>
            </thead>
            <tbody>
          <?php
          if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
              $dt_payday = datefromsql($row["payday"]);
              $dt_limit = datefromsql($row["limitday"]);
              $dt_now = new DateTime(date("Y-m-d"));

              $interval_now_limit = $dt_now->diff($dt_limit);
              $interval_pay_now = $dt_now->diff($dt_payday);
              $days_now_limit = $interval_now_limit->days;
              $days_now_pay = $interval_pay_now->days;

              $disabled = "";
              $class_paid = "";
              $type = "paid";
              if($row["paid"]==1){
                $disabled = "disabled";
                $class_paid = "paid";
                $status = "<span class='text-secondary'>◉ Pago</span>";
              }else{
                if($dt_now>=$dt_payday){
                  if($dt_now>$dt_limit){
                    $type = "vencido";
                    $status = "<span class='text-danger'>◉</span> Vencido (".$interval_now_limit->format('%R%a')." d)";
                  }else{
                    $type = "avencer";
                    $status = "<span class='text-warning'>◉</span> A vencer (".$interval_now_limit->format('%R%a')." d)";
                  }
                }else{
                    $type = "afaturar";
                    $status = "<span class='text-success'>◉</span> A faturar (Previsão: ".$interval_pay_now->format('%R%a')."d)";
                }
              }


              $checked = "";
              if($row["reason"]==0 && $row["paid"]==0){
                $checked = "checked";
              }

              echo '<tr><td><div class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input check" id="'.$type.'" value="'.$row["cash_value"].'" name="checkbox" '.$checked.' '.$disabled.'>
                  <label class="custom-control-label" for="'.$type.'"></label>
              </div></td>';

              $emojis = '<a class="link" href="pay.php?id='.$row["id"].'&return='.urlencode($_SERVER['REQUEST_URI']).'&unpay=1">🧾</a>';
              if($row["paid"] == 0){
                $modal_args = ' data-bs-toggle="modal" data-bs-target="#modalEdit" data-name="'.$row["name"].'" data-id="'.$row["id"].'" data-cash="'.$row["cash_value"].'" data-payday="'.$dt_payday->format('d/m/Y').'" data-limit="'.$dt_limit->format('d/m/Y').'" data-optional="'.$row["reason"].'" data-action="edit"';
                $emojis = '<a href="#" class="link" '.$modal_args.'>📝</a> <a class="link" href="remove.php?id='.$row["id"].'&return='.urlencode($_SERVER['REQUEST_URI']).'">✖️</a> <a class="link" href="pay.php?id='.$row["id"].'&return='.urlencode($_SERVER['REQUEST_URI']).'">💸</a>';
              }
              echo '<td>'.$row["id"].'</td><td><span class="'.$class_paid.'">'.$row["name"].'</span></td><td>R$ '.$row["cash_value"].'</td><td>'.$dt_payday->format('d/m/Y').'</td><td>'.$dt_limit->format('d/m/Y').'</td><td>'.$status.'</td><td>'.$emojis.'</td></tr>';
            }
          }
          ?>
            </tbody>
          </table>
          <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-3" style="margin-top:3vh; background-color:#FBFEA3">
              <table class="table">
                <tbody>
                  <tr>
                    <th><span class="text-success">A Faturar: </span></th>
                    <td>R$ <span id="total-afaturar">0.00</span></td>
                  </tr>
                  <tr>
                    <th><span class="text-warning">A Vencer: </span></th>
                    <td>R$ <span id="total-avencer">0.00</span></td>
                  </tr>
                  <tr>
                    <th><span class="text-danger">Vencido: </span></th>
                    <td>R$ <span id="total-vencido">0.00</span></td>
                  </tr>
                  <tr>
                    <th><span>Total: </span></th>
                    <td>R$ <span id="total-total">0.00</span></td>
                  </tr>
                </tbody>
              </table>
          </div>
        </div>
        </div>
        <?php
      }
    ?>

    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Editando</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form method="get" action="edit.php" name=modal>
              <div class="mb-3">
                <label for="id" class="col-form-label">ID:</label>
                <input type="hidden" class="form-control" id="id" name="id">
                <input type="hidden" class="form-control" id="anomes" name="anomes" value="<?php echo $_GET["anomes"]; ?>">
                <input type="hidden" class="form-control" id="return" name="return" value="<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">
                <input type="text" class="form-control" id="iddisabled" name="iddisabled" disabled>
              </div>
              <div class="mb-3">
                <label for="name" class="col-form-label">Name:</label>
                <input type="text" class="form-control" id="name" name="name">
              </div>
              <div class="mb-3">
                <label for="cash" class="col-form-label">Cash Value:</label>
                <input type="text" class="form-control" id="cash" name="cash">
              </div>
              <div class="mb-3">
                <label for="payday" class="col-form-label">Payday:</label>
                <input type="text" class="form-control" id="payday" name="payday">
              </div>
              <div class="mb-3">
                <label for="limit" class="col-form-label">Limitday:</label>
                <input type="text" class="form-control" id="limit" name="limit">
              </div>
              <div class="mb-3">
                <input class="form-check-input" type="checkbox" value="optional" id="optional" name="optional" checked>
                <label class="form-check-label" for="optional">
                  Optional
                </label>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>



    <div class="finish"></div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-u1OknCvxWvY5kfmNBILK2hRnQC3Pr17a+RTT6rIHI7NnikvbZlHgTPOOmMi466C8" crossorigin="anonymous"></script>
    <script  src="https://code.jquery.com/jquery-3.6.1.js"  integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script>



      var exampleModal = document.getElementById('modalEdit');
      exampleModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget

        var action = button.getAttribute('data-action');

        if(action == "add"){
          document.querySelector("form[name='modal']").action = document.querySelector("form[name='modal']").action.replace("edit.php", "add.php");

          exampleModal.querySelector('.modal-title').textContent = "New bill.";
          exampleModal.querySelector(".modal-body input[name='id']").value = "";
          exampleModal.querySelector(".modal-body input[name='iddisabled']").value = "";
          exampleModal.querySelector(".modal-body input[name='name']").value = "";
          exampleModal.querySelector(".modal-body input[name='cash']").value = "";
          exampleModal.querySelector(".modal-body input[name='payday']").value = "";
          exampleModal.querySelector(".modal-body input[name='limit']").value = "";
          exampleModal.querySelector(".modal-body input[name='optional']").checked = true;
        }else{
          document.querySelector("form[name='modal']").action = document.querySelector("form[name='modal']").action.replace("add.php", "edit.php");
          var name = button.getAttribute('data-name');
          var id = button.getAttribute('data-id');
          var cash = button.getAttribute('data-cash');
          var payday = button.getAttribute('data-payday');
          var limit = button.getAttribute('data-limit');
          var optional = button.getAttribute('data-optional');

          exampleModal.querySelector('.modal-title').textContent = "Editando (" + id +") "+name;
          exampleModal.querySelector(".modal-body input[name='id']").value = id;
          exampleModal.querySelector(".modal-body input[name='iddisabled']").value = id;
          exampleModal.querySelector(".modal-body input[name='name']").value = name;
          exampleModal.querySelector(".modal-body input[name='cash']").value = cash;
          exampleModal.querySelector(".modal-body input[name='payday']").value = payday;
          exampleModal.querySelector(".modal-body input[name='limit']").value = limit;

          if(optional==1){
            exampleModal.querySelector(".modal-body input[name='optional']").checked = true;
          }else{
            exampleModal.querySelector(".modal-body input[name='optional']").checked = false;
          }

        }




      });





    $(document).ready( function () {
      $('#dataTable').DataTable({
                paging: false,
                info: false,
      });
    } );

    function changeTotal(){
      var checkedBoxes = document.querySelectorAll('input[name=checkbox]:checked');
      var valueafaturar = 0;
      var valueavencer = 0;
      var valuevencido = 0;
      var valuetotal = 0;
      [].forEach.call(checkedBoxes, function(check) {
        if(check.id == "vencido"){
          valuevencido += parseFloat(check.value);
        }
        if(check.id == "avencer"){
          valueavencer += parseFloat(check.value);
        }
        if(check.id == "afaturar"){
          valueafaturar += parseFloat(check.value);
        }
        valuetotal += parseFloat(check.value);

      });

      document.getElementById('total-total').innerHTML = Math.round(valuetotal * 100) / 100;
      document.getElementById('total-afaturar').innerHTML = Math.round(valueafaturar * 100) / 100;
      document.getElementById('total-avencer').innerHTML = Math.round(valueavencer * 100) / 100;
      document.getElementById('total-vencido').innerHTML = Math.round(valuevencido * 100) / 100;

    }

    $(".check").on("click change", function() {
       changeTotal();
    });
    changeTotal();
    </script>
  </body>
</html>

<?php
$conn->close();
?>
