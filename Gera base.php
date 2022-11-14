<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dados Performance </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
  </head>
  <body>

  <h1 class="text-center" >Mailing  & Base</h1>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
 
    <div class="w-75 p-3" style="margin-left: 30px;">
      <form action="?" method="post">

        <label for="exampleFormControlTextarea1" class="form-label"><b>Selecione a empresa</b></label>
        <select class="form-select" name="empresa" id="empresa" aria-label="Default select example">
          <option value="Null">Escolha</option>
          <option value="RENOVABE">RENOVABE</option>
          <option value="100PESO">100PESO</option>
          <option value="NEWWHITE">NEWWHITE</option>
          <option value="NEWHAIR">NEWHAIR</option>          
        </select>

        <br />
        
        <label for="exampleFormControlTextarea1" class="form-label"><b>Tipo da Base</b></label>
        <select class="form-select" name="tipobase" aria-label="Default select example">
          <option value="Null">Escolha</option>
          <option value="Base_SMS">Base_SMS</option>
          <option value="Base_Completa">Base_Completa</option>         
        </select>

        <br />
        <div class="d-flex">
          <div class="w-50">
            <label for="exampleFormControlTextarea1" class="form-label"><b>Data Inicio (Ex:Ano-Mes-Dia)</b></label>
            <input class="form-control me-2" name="dataini" id="dataini" type="search" placeholder="Search" aria-label="Search">
          </div>
          
          <div class="w-50">
            <label for="exampleFormControlTextarea1" class="form-label"><b>Data Fim (Ex:Ano-Mes-Dia)</b></label>
            <input class="form-control me-2" name="datafim" id="datafim" type="search" placeholder="Search" aria-label="Search">
          </div>
        </div>

        
        <br /><br /><br />
        
        <button type="submit" name="Base_Completa" class="btn btn-primary">Base Completa</button>
  
        <?php
          $nomearquivo = $_POST['empresa'];
        ?>
        
      </form>

      <br />
      <br />
      <a href="Base_clientes_<?= $nomearquivo ?>.csv" target="_blank" class="btn btn-primary">Download Arquivo</a>
      
    </div> 

<?php

if ($_POST['tipobase'] == 'Base_Completa'){

  unlink('Base_clientes_'. $_POST['empresa'].'.csv');

  $mysqlli = mysqli_connect('IP', 'Banco', 'Senha', 'Data');

  if ($_POST['dataini'] == ' ' or $_POST['datafim'] == ' ' or $_POST['empresa'] == 'Null'){
    
   
    ob_flush($_POST['empresa'],$_POST['mes']);
    flush($_POST['empresa'],$_POST['mes']);
     echo 'Colocar o Mes!!';

  }

  else{
  $sql = 'SELECT 
            *
          from vw_cliente
          WHERE Grupo = "'. $_POST['empresa'].'"
          and Data_UltimoPedido >= "'.$_POST['dataini'].'"
          and Data_UltimoPedido <= "'.$_POST['datafim'].'"';
       
  $dados = mysqli_query($mysqlli,$sql);

  $cabecalho = 'Ultimo_Pedido,Data_UltimoPedido,Grupo,Site,Status_ultimoPedido,CPF,Nome_Completo,primeiro_nome,sobre_nome,Email,Celular,Telefone,CEP,Cidade,Estado,dtnasci,idade,pedidos_cliente_sexo,Total_Pedidos,valor_gasto'. "\n";

  $arquivo = fopen('Base_clientes_'. $_POST['empresa'].'.csv','w');
  fwrite($arquivo, $cabecalho);

  while ($row = mysqli_fetch_array($dados, MYSQLI_ASSOC)){

            fputcsv($arquivo,$row);

    }

  fclose($arquivo);

  echo 'Base Gerada';

  }
}
else{
# parte de gerar SMS

$mysqlli = mysqli_connect('IP', 'Banco', 'Senha', 'Data');;

  if ($_POST['dataini'] == ' ' or $_POST['datafim'] == ' ' or $_POST['empresa'] == 'Null'){
      
    
    ob_flush($_POST['empresa'],$_POST['mes']);
    flush($_POST['empresa'],$_POST['mes']);
    echo 'Colocar o Mes!!';

  }

  else{
  $sql = 'SELECT 
            Telefone
          from vw_cliente
          WHERE Grupo = "'. $_POST['empresa'].'"
          and Data_UltimoPedido >= "'.$_POST['dataini'].'"
          and Data_UltimoPedido <= "'.$_POST['datafim'].'"';
      
  $dados = mysqli_query($mysqlli,$sql);

  $cabecalho = 'Telefone'. "\n";

  $arquivo = fopen('Base_clientes_'. $_POST['empresa'].'.csv','w');
  fwrite($arquivo, $cabecalho);

  while ($row = mysqli_fetch_array($dados, MYSQLI_ASSOC)){

            fputcsv($arquivo,$row);

    }

  fclose($arquivo);

  echo 'Base Gerada';

  }
}
?>
</body>
</html>