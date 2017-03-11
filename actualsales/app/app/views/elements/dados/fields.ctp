<div class='well'>
    <strong>Código: </strong><?php echo $this->Html->tag('span', $this->data['Cliente']['codigo']); ?>
    <strong>Cliente: </strong><?php echo $this->Html->tag('span', $this->data['Cliente']['razao_social']); ?>
    <?php echo $this->BForm->hidden('Setor.codigo_cliente', array('value' => $this->data['Cliente']['codigo'])); ?>
</div>
<div class='row-fluid inline'>
	<?php echo $this->BForm->input('Setor.descricao', array('label' => 'Descrição (*)', 'class' => 'input-xxlarge')); ?>
	<?php echo $this->BForm->input('Setor.codigo_rh', array('label' => 'Código RH', 'class' => 'input-large')); ?>

	<?php 
	if(empty($this->passedArgs[2])): 
		echo $this->BForm->hidden('Setor.ativo', array('value' => 1)); 
	else: 
		echo $this->BForm->input('Setor.ativo', array('label' => 'Status (*)', 'class' => 'input', 'default' => '', 'empty' => 'Status', 'options' => array(1 => 'Ativo', 0 => 'Inativo'))); 
	endif; ?>
	<?php echo $this->BForm->hidden('Setor.codigo', array('value' =>  !empty($this->data['Setor']['codigo'])? $this->data['Setor']['codigo'] : '')); ?>
</div>
<div class='row-fluid inline'>
	<?php echo $this->BForm->input('Setor.descricao_setor', array('label' => 'Descrição do Setor', 'class' => 'input-xxlarge', 'type' => 'textarea')); ?>
	<?php echo $this->BForm->input('Setor.observacao_aso', array('label' => 'Observação ASO', 'class' => 'input-xxlarge', 'type' => 'textarea')); ?>
</div>
 <div class='form-actions'>
	<?php echo $this->BForm->submit('Salvar', array('div' => false, 'class' => 'btn btn-primary')); ?>
	<?php echo $html->link('Voltar', array('controller' => 'setores', 'action' => 'index', $this->data['Cliente']['codigo'], $referencia), array('class' => 'btn')); ?>
</div>
