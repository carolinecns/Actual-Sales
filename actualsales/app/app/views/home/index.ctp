<?php if(!empty($dados)): ?>
    <table class="table table-striped">
        <thead>
            <tr>
               <th class="input-medium">Código</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Data de Nascimento</th>
            <th>Região</th>
            <th>Unidade</th>
            <th class="acoes" style="width:75px">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados as $dado): ?>
            <tr>
                <td class="input-mini"><?php echo $dado['Cadastro']['codigo'] ?></td>
                <td><?php echo $dado['Cadastro']['nome'] ?></td>
                <td><?php echo $dado['Cadastro']['email'] ?></td>
                <td><?php echo $dado['Cadastro']['data_nascimento'] ?></td>
                <td><?php echo $dado['Regiao']['nome'] ?></td>
                <td><?php echo $dado['Unidade']['nome'] ?></td>
                <td>
                <?php echo $this->Html->link('', 'javascript:void(0)',array('class' => 'icon-random troca-status', 'escape' => false, 'title'=>'Troca Status','onclick' => "atualizaStatus('{$dado['Cadastro']['codigo']}','{$dado['Cadastro']['email']}')"));?>

                <?php if($dado['Cadastro']['email']== 0): ?>
                    <span class="badge-empty badge badge-important" title="Desativado"></span>
                <?php elseif($dado['Cadastro']['email']== 1): ?>
                    <span class="badge-empty badge badge-success" title="Ativo"></span>
                <?php endif; ?>
                
                <?php echo $this->Html->link('', array('action' => 'detalhes', $dado['Cadastro']['codigo']), array('class' => 'glyphicon glyphicon-th-large ', 'title' => 'Detalhes')); ?>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
        <tfoot>
            <tr>
                <td colspan = "10"><strong>Total</strong> <?php echo count($dados); ?></td>
            </tr>
        </tfoot>    
    </table>
<?php else:?>
    <div class="alert">Nenhum dado foi encontrado.</div>
<?php endif;?> 