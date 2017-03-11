    <?print_r($dados);?>
    
    <div class="container-fluid bg-2 text-left">    
      <div class="row" style="border-bottom:1px solid #ccc">
        <div class="col-sm-5">
          <p><strong>Nome</strong></p>
        </div>
        <div class="col-sm-10">
          <p><?=$dados['Cadastro']['nome']?></p>
        </div>
      </div>
      <div class="row" style="border-bottom:1px solid #ccc">
        <div class="col-sm-5">
          <p><strong>Data de Nascimento</strong></p>
        </div>
        <div class="col-sm-10"> 
          <p><?=$dados['Cadastro']['data_nascimento']?></p>
        </div>
      </div>
      <div class="row" style="border-bottom:1px solid #ccc">
        <div class="col-sm-5">
          <p><strong>E-mail</strong></p>
        </div>
        <div class="col-sm-10">
          <p><?=$dados['Cadastro']['email']?></p>
        </div>
      </div>
      <div class="row" style="border-bottom:1px solid #ccc">
        <div class="col-sm-5">
          <p><strong>Telefone</strong></p>
        </div>
        <div class="col-sm-10">
          <p><?=$dados['Cadastro']['telefone']?></p>
        </div>
      </div>
      <div class="row" style="border-bottom:1px solid #ccc">
        <div class="col-sm-5">
          <p><strong>Região</strong></p>
        </div>
        <div class="col-sm-10">
          <p><?=$dados['Regiao']['nome']?></p>
        </div>
      </div>
       <div class="row" style="border-bottom:1px solid #ccc">
        <div class="col-sm-5">
          <p><strong>Unidade</strong></p>
        </div>
        <div class="col-sm-10">
          <p><?=$dados['Unidade']['nome']?></p>
        </div>
      </div>
       <div class="row" style="border-bottom:1px solid #ccc">
        <div class="col-sm-5">
          <p><strong>Score</strong></p>
        </div>
        <div class="col-sm-10">
          <p><?=$dados['Cadastro']['score']?></p>
        </div>
      </div>
    </div>
    <?php echo $html->link('Voltar', array('action' => 'index'), array('class' => 'button btn btn-info', 'role' => 'button', 'style' => 'margin-top: 30px;margin-left: 15px;')); ?>
