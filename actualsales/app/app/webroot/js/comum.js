if (typeof(window.console)=='undefined'){window.console={log:function(){},debug:function(){},info:function(){},dir:function(){}}}
 
$(document).ajaxError(function(e, jqxhr, settings, exception) {
	if (jqxhr.statusText == 'abort') return;
	$(this).trigger('loaderror', arguments);
});

jQuery(document).ready(function(){
	jQuery(document).on('click', '#modal_dialog .cancel', function(){
		close_dialog();
			return false;
	});

	$(document).ready(function() {
		$('[data-toggle=\"tooltip\"]').tooltip();

	})
	
	$(document)  
	.on("keydown", ".evt-endereco-cep", function(e) {
		if (e.keyCode == '13') {
			e.preventDefault();
			buscar_cep2(this, ".evt-endereco-codigo");
			$(this).parent().parent().find(".evt-endereco-numero").focus();
		}
	})
	.on("blur", ".evt-endereco-cep", function() {
		buscar_cep2(this, ".evt-endereco-codigo");
	});

	$(document).on("change", ".evt-carrega-cidade", function() {
		buscar_cidade(this, ".evt-cidade");
	});

	$(document).on("change", ".evt-carrega-bairro", function() {
		buscar_bairro(this, ".evt-bairro");
	});

	$(document).on("blur", ".evt-carrega-dados", function() {
		buscar_dados_endereco(this, ".evt-estado");
	});
});


function vericarCEPUnico( input_cep ){
	if ( parseInt(input_cep.val()) > 0){
		$.post(
			baseUrl + 'enderecos/buscar_endereco_cep/' + input_cep.val(),
			function(data) {
				if( data.VEndereco.endereco_cidade_cep_unico ) {
					var a = $("<a class='btn btn-mini btn-primary'>Criar novo endereço</a>").click(function(event){ 
						open_dialog(baseUrl + 'enderecos/incluir/'+input_cep.val()+'/'+input_cep.attr('id'), 'Criar novo endereço');
					});
					input_cep.parent().append(
						$('<div class="control-group error cep-nao-encontrado" style="clear:both">')
						.append("<div class='help-inline' style='padding: 0;'><b>CEP Único:</b> Todos os endereços serão carregados no combo acima .<br>Caso nenhuma das sugestões seja a correta,clicar em criar novo endereço.</div>").append(a)
					);
				}
			},'Json'
		);
	}
}

function verificarCEPUnico( input_cep ){
	if ( parseInt(input_cep.val()) > 0){
		$.post(
			baseUrl + 'enderecos/buscar_endereco_cep/' + input_cep.val(),
			function(data) {
				if( data.VEndereco.endereco_cidade_cep_unico ) {
					var a = $("<a class='btn btn-mini btn-primary'>Criar novo endereço</a>").click(function(event){ 
						open_dialog(baseUrl + 'enderecos/incluir/'+input_cep.val()+'/'+input_cep.attr('id'), 'Criar novo endereço');
					});					
					$(input_cep).parent().parent().append(
						$('<div class="control-group error cep-nao-encontrado" style="clear:both">')
						.append("<div class='help-inline' style='padding: 0;'><b>CEP Único11:</b> Todos os endereços serão carregados no combo acima .<br>Caso nenhuma das sugestões seja a correta,clicar em criar novo endereço.</div>").append(a)
					);
				}
			},'Json'
		);
	}
}

/*Se o cep nao for encontrado habilitara a botao de inclusao de cep*/
function buscar_cep2(input_cep, combo_class_receiver, codigo) {	
	input_cep = $(input_cep);		
	if ( parseInt(input_cep.val()) > 0){
		$.post(
			baseUrl + 'enderecos/listar_por_cep/' + input_cep.val() + '/' + Math.random(),
			function(data) {
				var comboEndereco = input_cep.parent().parent().parent().find(combo_class_receiver);
				comboEndereco.html(data);
				id = input_cep.parent().parent().parent().parent().attr('id');				
				//Verificar a quantidade de options				
				id_combo 	   = input_cep.parent().parent().parent().find(".select").find("select").attr("id");
				elemento_combo = document.getElementById( id_combo );
	    		qtde_options   = elemento_combo.options.length;
	    		if ( qtde_options < 2 ) {
	    			input_cep.parent().parent().removeClass('error');
					input_cep.parent().parent().find(".error-message").remove();	    			
	    			$('#lbl-error').remove();
					input_cep.addClass('form-error').parent().addClass('error').append('<div id="lbl-error" class="help-block">CEP inválido</div>');
	    			
	    		} else {
	    			$('.cep-nao-encontrado').remove();
	    			vericarCEPUnico( input_cep );
	    			$('#lbl-error').remove();
	    			input_cep.parent().removeClass('error');
	    			input_cep.parent().removeClass('error-message');	    			
	    		}
				if (typeof codigo != "undefined") {
					comboEndereco.val(codigo);
				}
			}
		);
	} else {
		$('.cep-nao-encontrado').remove();
	}
}

function sleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  }
}  

function pegar_dados_modal_cep_para_tela(id_retorno,codigo_escolhido){
	//Por causa do tempo de execução teve que se colocar + 1 no codigo do escolhido para dar certo
	codigo_escolhido = codigo_escolhido + 1;
	//console.log('inicio');
	sleep(1000000);
	//console.log('fim');
	if( $('#modal_dialog .error-message').length < 1 ) {
		cep = parseInt( $('#EnderecoCepCep').val());
		$(id_retorno).val(cep);
		id_combo = $(id_retorno).parent().parent().find(".select").find("select").attr("id");
		$.ajax({
			url:baseUrl + 'enderecos/listar_por_cep/' + cep + '/' + Math.random(),
			success: function(data) {
				if(data){					
					var comboEndereco = $("#"+id_combo);
					comboEndereco.html(data);
					$('.cep-nao-encontrado').remove();
				   
				}			
			},
			complete: function(){
				//Checando valores
				if ($('#EnderecoNomeBairro').val()!='' &&  $('#EnderecoCodigoEnderecoTipo').val()!='' &&  $('#EnderecoDescricao').val()!='') {
					$("#modal_dialog").dialog("close");
				}
				if (id_combo =="ProfissionalEnderecoCodigoEndereco"){
		           //id_cep = $('#ProfissionalEnderecoEnderecoCep');
		           //id_combo_nome =  $('#ProfissionalEnderecoCodigoEndereco');
		           buscar_cep($("#ProfissionalEnderecoEnderecoCep"),$("#ProfissionalEnderecoCodigoEndereco"),codigo_escolhido);
				}
				if (id_combo =="FichaScorecardVeiculo0ProprietarioEnderecoCodigoEndereco"){
		           //id_cep = $('#FichaScorecardVeiculo0ProprietarioEnderecoEnderecoCep'); 
		           //id_combo_nome =  $('#FichaScorecardVeiculo0ProprietarioEnderecoCodigoEndereco');
                   buscar_cep($("#FichaScorecardVeiculo0ProprietarioEnderecoEnderecoCep"),$("#FichaScorecardVeiculo0ProprietarioEnderecoCodigoEndereco"),codigo_escolhido);
				}
				if (id_combo =="FichaScorecardVeiculo1ProprietarioEnderecoCodigoEndereco"){
		           //id_cep = $('#FichaScorecardVeiculo1ProprietarioEnderecoEnderecoCep'); 
		           //id_combo_nome =  $('#FichaScorecardVeiculo1ProprietarioEnderecoCodigoEndereco');
				   buscar_cep($("#FichaScorecardVeiculo1ProprietarioEnderecoEnderecoCep"),$("#FichaScorecardVeiculo1ProprietarioEnderecoCodigoEndereco"),codigo_escolhido);
				}
				if (id_combo =="FichaScorecardVeiculo2ProprietarioEnderecoCodigoEndereco"){
		           //id_cep = $('#FichaScorecardVeiculo2ProprietarioEnderecoEnderecoCep'); 
		           //id_combo_nome =  $('#FichaScorecardVeiculo1ProprietarioEnderecoCodigoEndereco');
		           buscar_cep($("#FichaScorecardVeiculo2ProprietarioEnderecoEnderecoCep"),$("#FichaScorecardVeiculo2ProprietarioEnderecoCodigoEndereco"),codigo_escolhido);
				}
			}
		});
	}
}

function preencher_endereco_from_modal(campo_retorno,codigo_endereco){
	if( $('#modal_dialog .error-message').length < 1 ) {
		campo_retorno = $(campo_retorno);
		cep = campo_retorno.val();
        bloquearDiv(campo_retorno.parent().parent());
		$.post(
            baseUrl + 'enderecos/listar_por_cep/' + cep + '/' + Math.random(),
            function(data) {
				campo_endereco = campo_retorno.parent().parent().find(".codigo_endereco");
                campo_endereco.html(data);
                campo_endereco.val(codigo_endereco);
                campo_retorno.parent().parent().find('.cep-nao-encontrado').remove();
                campo_retorno.parent().parent().unblock();
            }
        );
	}
}

function buscar_cep(input_cep, combo_class_receiver, codigo) {
	
	input_cep = $(input_cep);
	$.post(
		baseUrl + 'enderecos/listar_por_cep/' + input_cep.val() + '/' + Math.random(),
		function(data) {
			var comboEndereco = input_cep.parent().parent().find(combo_class_receiver);
			comboEndereco.html(data);
			
			if (typeof codigo != "undefined") {
				comboEndereco.val(codigo);
			}
		}
	);
}

function contadorChar(input_field,display,maxlength){
	input_field = $(input_field);
	display 	= $(display);

	$(input_field).ready(function(){
		var result 	= maxlength - input_field.val().length;

		if(display.is('input'))
			display.val(result);
		else
			display.html(result);
	});
	

	$(input_field).keydown(function(e){
		var result 	= maxlength - input_field.val().length;

		if(e.which != 8 && 
		   e.which != 9 &&
		   e.which != 20 &&
		   e.which != 18 &&
		   e.which != 46 &&
		   e.which != 17){
			
			if(result <= 0)
				return false;
		}
	});

	$(input_field).keyup(function(e){
		var result 	= maxlength - input_field.val().length;

		if(result >= 0){
			if(display.is('input'))
				display.val(result);
			else
				display.html(result);
		}

	});

	$(document).on('focusout',input_field,function(){
		var result 	= maxlength - input_field.val().length;	

		if(result < 0){
			input_field.val(input_field.val().substring(0,maxlength));
			result = 0;
		}

		if(display.is('input'))
			display.val(result);
		else
			display.html(result);
	});

}

function buscar_endereco(input_cep) {
	input_cep = $(input_cep);
	
	$.ajax({
		url:baseUrl + 'enderecos/busca_endereco/' + input_cep.val() + '/' + Math.random(),
		dataType: 'json',
		beforeSend: function(){
			input_cep.addClass('ui-autocomplete-loading');
		},
		success: function(data) {
			input_cep.parent().removeClass('error');
			input_cep.parent().find('.help-block').remove();
			
			if(data){
				var cidade = $('input.cidade');
				if(cidade.length > 0)
					cidade.val(data.endereco_cidade);

				var estado = $('input.estado');
				if(estado.length > 0)
					estado.val(data.endereco_estado_abreviacao);

				var endereco = $('input.endereco');
				if(endereco.length > 0)
					endereco.val(data.endereco_tipo + ' ' + data.endereco_logradouro);

				var bairro = $('input.bairro');
				if(bairro.length > 0)
					bairro.val(data.endereco_bairro);
				
			} else {
				input_cep.parent().addClass('error');
				input_cep.parent().append('<div class="help-block error-message">Endereço não localizado</div>')
			}
			
		},
		complete: function(){
			input_cep.removeClass('ui-autocomplete-loading');
		}
	});
}

function buscar_endereco_maplink(input_cep) {
	input_cep = $(input_cep);
	
	$.ajax({
		url:baseUrl + 'enderecos/busca_endereco_maplink/' + input_cep.val() + '/' + Math.random(),
		dataType: 'json',
		beforeSend: function(){
			input_cep.addClass('ui-autocomplete-loading');
		},
		success: function(data) {
			if(data){
				var cidade = $('input.cidade');
				if(cidade.length > 0)
					cidade.val(data.city.name);

				var estado = $('input.estado');
				if(estado.length > 0)
					estado.val(data.city.state);

				var endereco = $('input.endereco');
				if(endereco.length > 0)
					endereco.val(data.street);

				var bairro = $('input.bairro');
				if(bairro.length > 0)
					bairro.val(data.district);
				
			} else {
				alert('Endereço não localizado');
			}
			
		},
		complete: function(){
			input_cep.removeClass('ui-autocomplete-loading');
		}
	});
}

function busca_profissional(element_cpf, element_nome, element_codigo){
	var cpf 	= $(element_cpf);
	var nome 	= $(element_nome);
	var codigo 	= $(element_codigo);

	cpf.blur(function(){
		if(cpf.val()){
			$.ajax({
				url: baseUrl + 'Profissionais/carregarPorCpf/' + cpf.val() + '/' + Math.random(),
				type: 'post',
				dataType: 'json',
				beforeSend: function(){
					cpf.addClass('ui-autocomplete-loading');
				},
				success: function(data){
					if(data){
						nome.val(data.Profissional.nome);
						codigo.val(data.Profissional.codigo);
					} else {
						nome.val('');
						codigo.val('');
					}
				},
				complete: function(){
					cpf.removeClass('ui-autocomplete-loading');
				}
			});
		} else {
			nome.val('');
			codigo.val('');
		}
	});
}

function busca_problema(element_tipo, content){
	var tipo 		= $(element_tipo);
	var content 	= $(content);
	content.html('<option value="">Aguarde...</option>');
	content.load(baseUrl+'problemas/listar_por_tipo/'+tipo.val()+'/'+Math.random());
}

function ver_mapa(element_lat,element_long,element_raio,acao){	

	$(element_lat).blur(function(){
		carregar_mapa(element_lat,element_long,element_raio);
	});

	$(element_long).blur(function(){
		carregar_mapa(element_lat,element_long,element_raio);
	});

	$(element_raio).blur(function(){
		carregar_mapa(element_lat,element_long,element_raio);
	});

	carregar_mapa(element_lat,element_long,element_raio);	
}

function carregar_mapa(element_lat,element_long,element_raio){
	var latitude 	= $(element_lat);
	var longitude 	= $(element_long);
	var raio 		= $(element_raio);

	if(latitude.val() && longitude.val()){
		$.ajax({
			url: encodeURI(baseUrl +'referencias/mapa/'+latitude.val()+'/'+longitude.val()+'/'+raio.val()+'/'+Math.random()),
			dataType: 'html',
			beforeSend: function(){
				var div = $("#canvas_mapa");
				bloquearDiv(div);
			},
			success: function(data){
				if(data)
					$("#canvas_mapa").html(data);
			}
		});
	} else {
		alert('Informe latitude e longitude corretamente');
	}
}

function autocomplete_escolta(model){
	
	$(".escolta-complete").each(function(){
		var name 	= $(this).attr('id');
		var count 	= $(this).parents('table:eq(0)').attr('data-index');

		if($(this).parents('tr:eq(0)').find('input.complete-id').length == 0){
			$(this).parents('td:eq(0),th:eq(0)').prepend("<input class='complete-id' type='hidden' value='' name='data["+model+"]["+count+"][completId]' />");
		}

		if($(this).parents('tr:eq(0)').find('input.complete-name').length == 0){
			$(this).parents('td:eq(0),th:eq(0)').prepend("<input class='complete-name' type='hidden' value='' name='data["+model+"]["+count+"][completName]' />");
		}
	});
	

	$(".escolta-complete").keydown(function(event){
		//console.log(event.keyCode);
		if(
			(event.keyCode < 33 || event.keyCode > 40)
			&& event.keyCode != 9
			&& event.keyCode != 16
			&& event.keyCode != 17
			&& event.keyCode != 18
			&& event.keyCode != 225
		){
			$(this).parents('tr:eq(0)').find('input.complete-id').val('');
			$(this).parents('tr:eq(0)').find('input.complete-name').val('');
		}
	});

	$(".escolta-complete").autocomplete({
		source:  baseUrl + "Escoltas/auto_completar/" + Math.random(),
		minLength: 1,
		focus: function(){return false;},
		select: function( event, ui ){
			var id		= $(this).parents('tr:eq(0)').find('input.complete-id');
			var name 	= $(this).parents('tr:eq(0)').find('input.complete-name');
			
			id.val(ui.item.value);
			name.val(ui.item.label);
			$(this).val(ui.item.label);

			return false;
		}
	});
}

function autocomplete_motorista(model,embarcador){
	
	$(".moto-complete").each(function(){
		var name 	= $(this).attr('id');
		
		if($(this).parents('div:eq(0)').find('input.complete-id').length == 0){
			$(this).parents('div:eq(0)').prepend("<input class='complete-id' type='hidden' value='' name='data["+model+"]["+name+"][completId]' />");
		}

		if($(this).parents('div:eq(0)').find('input.complete-name').length == 0){
			$(this).parents('div:eq(0),').prepend("<input class='complete-name' type='hidden' value='' name='data["+model+"]["+name+"][completName]' />");
		}
	});
	
	$(".moto-complete").change(function(){
		$(this).parents('div:eq(0)').find('input.complete-id').val('');
		$(this).parents('div:eq(0)').find('input.complete-name').val('');
	});

	$(".moto-complete").autocomplete({
		source: function (request, response) {
			$.ajax({
				url: baseUrl + "profissionais/autocomplete_motorista/" + embarcador + "/" + Math.random(),
				data: request,
				dataType: 'json',
				success: function (data) {
					data.push({label: "Novo", value: -1});
					response(data);
				},
				error: function () {
					response([]);
				}
			});
		},
		minLength: 1,
		focus: function(){return false;},
		select: function( event, ui ){
			var id		= $(this).parents('div:eq(0)').find('input.complete-id');
			var name 	= $(this).parents('div:eq(0)').find('input.complete-name');
			
			id.val(ui.item.value);
			name.val(ui.item.label);
			$(this).val(ui.item.label);

			return false;
		}
	}).data( "uiAutocomplete" )._renderItem = function( ul, item) {
		var li = $( "<li></li>" ).data( "item.autocomplete", item );
		if (item.label == "Nenhum") {
			li.append( "Nenhum item encontrado <br />" );
		} else if (item.label == "Novo") {
			var a = $("<a class='btn btn-link' style='color: #0088cc; text-decoration: underline;'>Adicionar Motorista</a>").click(function(event){ 
				event.preventDefault(); 
				var codigoCiente = jQuery('#RecebsmCodigoCliente').val();
				$('.refe-complete').autocomplete('close'); 
				open_dialog(baseUrl + 'profissionais/incluir', 'Adicionar Motorista', 900)
				return false; 
			});
			li.append(a);
		} else {
			li.append( "<a>"+ item.label + "</a>" )
		}
		return li.appendTo( ul );
	}

}

function autocomplete_t_motorista(model,embarcador){
	
	$(".moto-complete").each(function(){
		var name 	= $(this).attr('id');
		
		if($(this).parents('div:eq(0)').find('input.complete-id').length == 0){
			$(this).parents('div:eq(0)').prepend("<input class='complete-id' type='hidden' value='' name='data["+model+"]["+name+"][completId]' />");
		}

		if($(this).parents('div:eq(0)').find('input.complete-name').length == 0){
			$(this).parents('div:eq(0),').prepend("<input class='complete-name' type='hidden' value='' name='data["+model+"]["+name+"][completName]' />");
		}
	});
	
	$(".moto-complete").change(function(){
		$(this).parents('div:eq(0)').find('input.complete-id').val('');
		$(this).parents('div:eq(0)').find('input.complete-name').val('');
	});

	$(".moto-complete").autocomplete({
		source: function (request, response) {
			$.ajax({
				url: baseUrl + "profissionais/autocomplete_t_motorista/" + embarcador + "/" + Math.random(),
				data: request,
				dataType: 'json',
				success: function (data) {
					data.push({label: "Novo", value: -1});
					response(data);
				},
				error: function () {
					response([]);
				}
			});
		},
		minLength: 1,
		focus: function(){return false;},
		select: function( event, ui ){
			var id		= $(this).parents('div:eq(0)').find('input.complete-id');
			var name 	= $(this).parents('div:eq(0)').find('input.complete-name');
			
			id.val(ui.item.value);
			name.val(ui.item.label);
			$(this).val(ui.item.label);

			return false;
		}
	}).data( "uiAutocomplete" )._renderItem = function( ul, item) {
		var li = $( "<li></li>" ).data( "item.autocomplete", item );
		if (item.label == "Nenhum") {
			li.append( "Nenhum item encontrado <br />" );
		} else if (item.label == "Novo") {
			var a = $("<a class='btn btn-link' style='color: #0088cc; text-decoration: underline;'>Adicionar Motorista</a>").click(function(event){ 
				event.preventDefault(); 
				var codigoCiente = jQuery('#RecebsmCodigoCliente').val();
				$('.refe-complete').autocomplete('close'); 
				open_dialog(baseUrl + 'profissionais/incluir/a/', 'Adicionar Alvo', 600)
				return false; 
			});
			li.append(a);
		} else {
			li.append( "<a>"+ item.label + "</a>" )
		}
		return li.appendTo( ul );
	};
}

function buscar_t_cidade(input_estado, combo_class_receiver, codigo) {
	input_estado = $(input_estado);
	$.ajax({
		url: baseUrl + 'enderecos/carrega_combo_t_cidade/' + input_estado.val() + '/' + Math.random(),
		beforeSend: function(){
			$(combo_class_receiver).html('<option value="">Aguarde...</option>');
		},
		success: function(data) {
			var comboCidade = $(combo_class_receiver);
			comboCidade.html(data);

			if (typeof codigo != "undefined") {
				comboCidade.val(codigo);
			}
		}

	});
}

function buscar_t_estado(input_pais, combo_class_receiver, codigo) {
	input_pais = $(input_pais);
	$.ajax({
		url: baseUrl + 'enderecos/carrega_combo_t_estado/' + input_pais.val() + '/' + Math.random(),
		beforeSend: function(){
			$(combo_class_receiver).html('<option value="">Aguarde...</option>');
			$(combo_class_receiver).parent().parent().find('.cidade').html('<option value="">Cidade</option>');
		},
		success: function(data) {
			var comboEstado = $(combo_class_receiver);
			comboEstado.html(data);
			if (typeof codigo != "undefined") {
				comboEstado.val(codigo);
			}
		}
	});
}


function buscar_t_modelo(input_fabricante, combo_class_receiver, codigo) {
	input_fabricante = $(input_fabricante);
	$.ajax({
		url: baseUrl + 'modelos/carrega_combo_t_modelo/' + input_fabricante.val() + '/' + Math.random(),
		beforeSend: function(){
			$(combo_class_receiver).html('<option value="">Aguarde...</option>');
		},
		success: function(data) {
			var comboModelos = $(combo_class_receiver);
			comboModelos.html(data);
			
			if (typeof codigo != "undefined") {
				comboModelos.val(codigo);
			}
		}
		
	});
}

function buscar_t_versao(input_tecnologia, combo_class_receiver, codigo) {
	input_tecnologia = $(input_tecnologia);
	$.ajax({
		url: baseUrl + 'terminais/carregar_versao/' + input_tecnologia.val() + '/' + Math.random(),
		beforeSend: function(){
			$(combo_class_receiver).html('<option value="">Aguarde...</option>');
		},
		success: function(data) {
			var comboModelos = $(combo_class_receiver);
			comboModelos.html(data);
			if (typeof codigo != "undefined") {
				comboModelos.val(codigo);
			}
		}
		
	});
}

function buscar_modelo(input_fabricante, combo_class_receiver, codigo) {
	input_fabricante = $(input_fabricante);
	$.ajax({
		url: baseUrl + 'modelos/carrega_combo_modelo/' + input_fabricante.val() + '/' + Math.random(),
		beforeSend: function(){
			$(combo_class_receiver).html('<option value="">Aguarde...</option>');
		},
		success: function(data) {
			var comboModelos = $(combo_class_receiver);
			comboModelos.html(data);

			if (typeof codigo != "undefined") {
				comboModelos.val(codigo);
			}
		}

	});
}


function buscar_cidade(input_estado, combo_class_receiver, codigo) {
	input_estado = $(input_estado);
	$(combo_class_receiver).html('<option value="">Aguarde...</option>');

	if(input_estado.val()){
		$.post(
			baseUrl + 'enderecos/carrega_combo_cidade/' + input_estado.val() + '/' + Math.random(),
			function(data) {
				var comboCidade = $(combo_class_receiver);
				var comboBairro = $(comboCidade).next();
				comboCidade.html(data);

				if (typeof codigo != "undefined") {
					if (typeof codigo == "object") {
						comboCidade.val(codigo.EnderecoCidade.codigo);
						buscar_bairro(comboCidade, '.evt-bairro', codigo);
					} else {
						comboCidade.val(codigo);
					}	
				}
			}
		);
	} else {
		$(combo_class_receiver).html('<option value="">Cidade</option>');		
	}
	
}

function buscar_bairro(input_cidade, combo_class_receiver, codigo) {
	input_cidade = $(input_cidade);
	$.post(
		baseUrl + 'enderecos/carrega_combo_bairro/' + input_cidade.val() + '/' + Math.random(),
		function(data) {
			var comboBairro = $(combo_class_receiver);
			comboBairro.html(data);

			if (typeof codigo != "undefined") {
				comboBairro.val(codigo.Endereco.codigo_endereco_bairro_inicial);
			}
		}
	);
}

function buscar_dados_endereco(input_cep, combo_class_receiver) {
	input_cep = $(input_cep);
	if (input_cep.val() != "") {
		$.post(
			baseUrl + 'enderecos/buscarEnderecoPeloCep/' + input_cep.val() + '/' + Math.random(),
			function(data) {
				if (typeof data.Endereco != "undefined") {
					  $('#EnderecoCodigoEnderecoCep').val(data.Endereco.codigo_endereco_cep);
				}
			}, 'Json'
		);
	}
}

/**
 * Usa o blockUI para exibir o gif carregando enquanto algo ? processado em
 * background
 */
function bloquearDiv(div){
	$(div).block({
		message: '<img src="'+baseUrl+'img/loading.gif" />',
		overlayCSS:  {
			backgroundColor: '#000',
			opacity: 0.3
		},
		css: {
			border: 'none',
			padding: '10px',
			width: '17px',
			height: '17px',
			backgroundColor: '#fff',
			'-webkit-border-radius': '6px',
			'-moz-border-radius': '6px',
			opacity: 1,
			color: '#000'
		}
	});
}

function bloquearDivSemImg(div){
	div.block({
		message: '',
		overlayCSS:  {
			backgroundColor: '#000',
			opacity: 0.3
		},
		css: {
			border: 'none',
			padding: '10px',
			width: '17px',
			height: '17px',
			backgroundColor: '#fff',
			'-webkit-border-radius': '6px',
			'-moz-border-radius': '6px',
			opacity: 1,
			color: '#fff'
		}
	});
}

/**
 * Ajax gen?rico para salvar forms
 * 
 * @param form:
 *			nome do form que est? sendo postado
 * @param callback:
 *			funcao a ser chamada ap?s a opera??o. Se n?o for informada,
 *			atualiza a div pai do form
 * @return false
 */
function ajaxFormRequest(form, divupdate, bloquearDivPai, callback) {
	if(typeof(bloquearDivPai) == 'undefined'){
		bloquearDivPai = true;
	}
	
	if(typeof(callback) == 'undefined'){
		callback = function(){};
	}

	var div_pai = jQuery(divupdate);
	var data = jQuery(form).serialize();

	if (bloquearDivPai){
		bloquearDiv(div_pai);
	}

	jQuery.ajax({
		type: 'POST',
		url: jQuery(form).attr('action')+'/',
		data: data,
		success: function(html, textStatus) {
			div_pai.html(html);
			callback();
		}
	});
	return false;
}

function generate_modal_dialog() {
	if(jQuery("#modal_dialog").size() > 0)
		jQuery("#modal_dialog").remove();
	jQuery("body").append('<div id="modal_dialog" style="display:none"></div>');
}

function open_dialog(link, titulo, width, height, before_close, html, elemento, modal_group){
	var modal_link = '';
	var modal_params = {};
	var modal_params_default = {
	  modal: true,
	  resizable: false,
	  width: 600,
	  title: ""
	};
	
	generate_modal_dialog();
	if(typeof(width) != 'undefined')
		modal_params.width = width;
	if(typeof(height) != 'undefined')
		modal_params.height = height;
	if(typeof(before_close) != 'undefined')
		modal_params.beforeClose = before_close;
	if(typeof(titulo) == 'string')
		modal_params.title = titulo;
	if(typeof(link) == 'string')
		modal_link = link;
	else
		modal_link = link.href;

	if(typeof(elemento) != "object" && modal_group != "grupo_exposicao_risco"){
		scroll(0,0);
	}

	$.extend(modal_params_default, modal_params);
	//bloquearDiv($(".page-title").parent());
	if(typeof(html) != 'undefined' && html == true) {
		jQuery("#modal_dialog").html(link);
		jQuery("#modal_dialog").dialog(modal_params_default);
		$(".page-title").parent().unblock();
	} else {		
		jQuery("#modal_dialog").load([modal_link, '/', new Date().getTime()].join(''), function(){
			jQuery("#modal_dialog").dialog(modal_params_default);
		
			if(typeof(elemento) == "object" && modal_group == "grupo_exposicao_risco"){
				jQuery(".ui-dialog").position({
				  my: "center",
				  at: "center",
				  of: window
				});
			}

			var top = window.pageYOffset;
			var left = window.pageXOffset;
			scroll(left,top);
			$(".page-title").parent().unblock();
		});
	}
	return false;
}

function open_coord_map_dialog(titulo, map_params, width, height, api_config, before_close) {
	// var form_id = 'janela_mapa' + Math.random();
	var latitude = map_params.latitude || 0;
	var longitude = map_params.longitude || 0;
	var marker_title = map_params.marker_title || '';
	var url = encodeURI(baseUrl +'sistemas/janela_mapa/'+titulo+'/'+latitude+'/'+longitude+'/'+marker_title);
	var janela_mapa = window.open(url, 'janela_mapa', 'left=0,top=0,width='+width+',height='+height+',toolbar=0,scrollbars=0,menubar=0,location=0,status=0')
	return false;
}

function open_popup(element, largura, altura, isModal){
	if (!largura || !altura) {
		var janela = window_sizes();
		largura = janela.width-80;
		altura = janela.height-200;
	}
	var esquerda = ((screen.width - largura) / 2);
	var topo = ((screen.height - altura) / 2);
	if(isModal){
		window.showModalDialog(element.href,element.name,"dialogTop:"+topo+"px;dialogLeft:"+esquerda+"px;dialogHeight:"+altura+"px;dialogWidth:"+largura+"px;center:no;help:no;resizable:no;status:no;location:no;edge:Sunken;");
		window.location.reload();
	} else {
		var popup = window.open(element.href,element.name,"channelmode=0,directories=0,fullscreen=no,location=0,menubar=0,resizable=0,scrollbars=yes,status=0,titlebar=0,toolbar=0,top="+topo+"px,left="+esquerda+"px,width="+largura+"px,height="+altura+"px");
		popup.focus();
	}
	return false;
}


function close_dialog(flash){
	jQuery("#modal_dialog").dialog('close');
	jQuery("#modal_dialog").remove();
	if (flash != null) {
		jQuery("div.message").removeAttr('style').html(flash).show().delay(4000).slideUp();
	}
}

function post_parent_form(e){
	jQuery(e).parents('li').find('form').submit();
}

function setup_mascaras(mascaras){
	jQuery('.moeda').each(function() {
		moeda(this);
	}).keyup(function() {
		moeda(this);
	});

	jQuery('.moeda_com_decimal').each(function() {
		moeda_decimal(this);
	}).keyup(function() {
		moeda_decimal(this);
	});

	jQuery('.moeda_com_negativo').keyup(function() {
		moeda_com_negativo(this);
	});

	jQuery('.temperatura').keyup(function() {
		temperatura_negativo(this);
	});

	jQuery('.tempo').keyup(function() {
		tempo(this);
	});
	
	jQuery('.just-number').keyup(function() {
		just_number(this);
	});

	jQuery('.just-letters').keyup(function() {
		just_letters(this);
	});
	
	jQuery('.just-letters-without-special').keyup(function() {
		just_letters_without_special(this);
	});

	jQuery('.telefone').each( function(){
		if(!jQuery(this).hasClass('format-phone')){
			$(this).mask('(99)9999-99999').addClass('format-phone');
		}
	});
    
	jQuery('.cpf').each( function(){
		if(!jQuery(this).hasClass('format-cpf')){
			$(this).prop('size', 14).removeAttr('maxlength').addClass('format-cpf').mask('999.999.999-99');
			$(this).blur(function() {
				if (validarCPF($(this).val())) {
					$(this).removeClass('form-error').parent().removeClass('error').find('#lbl-error').remove();
				} else {
					if (!$(this).hasClass('form-error')) {
						$(this).addClass('form-error').parent().addClass('error').append('<div id="lbl-error" class="help-block error-message">CPF inválido</div>');
					}
				}
			})
		}
	});

   	    
    jQuery('.cnpj').each( function(){
		if(!jQuery(this).hasClass('format-cnpj')){
			$(this).prop('size', 18).removeAttr('maxlength').addClass('format-cnpj').mask('99.999.999/9999-99');
			$(this).blur(function() {
				if (validarCNPJ($(this).val())) {
					$(this).removeClass('form-error').parent().removeClass('error').find('#lbl-error').remove();
				} else {
					if (!$(this).hasClass('form-error')) {
						$(this).addClass('form-error').parent().addClass('error').append('<div id="lbl-error" class="help-block error-message">CNPJ inválido</div>');
					}
				}
			})
		}
	});

	jQuery('.cpf_cnpj').each( function(){
		if(!$(this).hasClass('format-cpf_cnpj')){
			$(this).blur(function() {
				$(this).removeClass('format-cpf_cnpj');					
				$(this).removeClass('form-error').parent().removeClass('error').find('#lbl-error').remove();
				codigo_documento = $(this).val().replace(/[^\d]+/g,'');				
				if( codigo_documento.length > 0 ){
					if( codigo_documento.length <= 11 ){
						$(this).prop('size', 14).removeAttr('maxlength').addClass('cpf_cnpj').mask('999.999.999-99');
						if (validarCPF($(this).val())) {
							$(this).removeClass('form-error').parent().removeClass('error').find('#lbl-error').remove();							
						} else {
							if (!$(this).hasClass('form-error')) {
								$(this).addClass('form-error').parent().addClass('error').append('<div id="lbl-error" class="help-block error-message">CPF inválido</div>');
							}
						}
					} else {
						$(this).prop('size', 18).removeAttr('maxlength').addClass('cpf_cnpj').mask('99.999.999/9999-99');
						if (validarCNPJ($(this).val())) {
							$(this).removeClass('form-error').parent().removeClass('error').find('#lbl-error').remove();
						} else {
							if (!$(this).hasClass('form-error')) {
								$(this).addClass('form-error').parent().addClass('error').append('<div id="lbl-error" class="help-block error-message">CNPJ inválido</div>');
							}
						}
					}
				}
				$(this).unmask();	
			})
		}
	});


	jQuery('.cnh').each( function(){
		$(this).blur(function() {
			if (validarCNH($(this).val())) {
				$(this).removeClass('form-error').parent().removeClass('error').find('#lbl-error').remove();
			} else {
				if (!$(this).hasClass('form-error')) {
					$(this).addClass('form-error').parent().addClass('error').append('<div id="lbl-error" class="help-block error-message">CHN inválida</div>');
				}
			}
		})
	});


	jQuery('.renavam').each( function(){
		$(this).blur(function() {
			if (validarRenavam($(this).val())) {
				$(this).removeClass('form-error').parent().removeClass('error').find('#lbl-error').remove();
			} else {
				if (!$(this).hasClass('form-error')) {
					$(this).addClass('form-error').parent().addClass('error').append('<div id="lbl-error" class="help-block error-message">Renavam inválido</div>');
				}
			}
		})
	});

	jQuery('.placa-veiculo').each( function(){
		if(!jQuery(this).hasClass('format-plate')){			
			$(this).mask('aaa-999?9').addClass('uppercase').addClass('format-plate');
		}
	});

    // função criada específica para carreteiro(fichascorecard)
    jQuery('.placaveiculo').each( function(){
		if(!jQuery(this).hasClass('format-plate')){
			$(this).mask('aaa-999?9').addClass('uppercase').addClass('format-plate');
		}
	});

   
	jQuery('.formata-cep').each( function(){
		if(!jQuery(this).hasClass('format-zip')){
			$(this).mask('99999999').addClass('format-zip');
		}
	});

	jQuery('.formata-cpf').each( function(){
		if(!jQuery(this).hasClass('formata-doc')){
			$(this).mask('99999999999').addClass('formata-doc');
		}
	});

	jQuery('.formata-rne').each( function(){
		if(!jQuery(this).hasClass('formata-doc')){
			jQuery.mask.definitions['~']='[A-Za-z0-9]';
			$(this).mask('~?~~~~~~~~~~~~~').addClass('formata-doc');
		}
	});

	jQuery('.ano_fabricacao_modelo').each( function(){
		if(!jQuery(this).hasClass('format-plate')){			
			$(this).mask('9999').addClass('uppercase').addClass('format-plate');
		}
	});
}

function tempo(z){
	v = z.value;
	v = v.replace(/\D/g,""); // Remove tudo o que não é dígito
	v = v.substring(0,3);
	z.value = v;
}

function temperatura_negativo(z){
	negativo = z.value.indexOf('-') >= 0;
	v = z.value;
	v = v.replace(/\D/g,""); // Remove tudo o que não é dígito
	v = v.substring(0,3);
	z.value = v;
	if(negativo)
		z.value = "-" + z.value;
}

function moeda_com_negativo(z){
	negativo = z.value.indexOf('-') >= 0;
	moeda(z);
	if(negativo)
		z.value = "-" + z.value;
}

function moeda(z){
	v = z.value;
	v = v.replace(/\D/g,""); // Remove tudo o que não é dígito

	v = v.replace(/(\d{2})$/,",$1"); // Coloca a virgula
	v = v.replace(/(\d+)(\d{3},\d{2})$/g,"$1.$2"); // Coloca o primeiro ponto

	var qtdLoop = (v.length-3)/3;
	var count = 0;

	while (qtdLoop > count) {
		count++;
		v = v.replace(/(\d+)(\d{3}.*)/,"$1.$2"); // Coloca o resto dos pontos
	}
	v=v.replace(/^(0)(\d)/g,"$2"); // Coloca hífen entre o quarto e o quinto
									// dígitos
	return z.value = v;
}

function moeda2(z){	
	var v = z.toFixed(2).toString();
	v = v.replace(/\D/g,""); // Remove tudo o que não é dígito

	v = v.replace(/(\d{2})$/,",$1"); // Coloca a virgula
	v = v.replace(/(\d+)(\d{3},\d{2})$/g,"$1.$2"); // Coloca o primeiro ponto

	var qtdLoop = (v.length-3)/3;
	var count = 0;

	while (qtdLoop > count) {
		count++;
		v = v.replace(/(\d+)(\d{3}.*)/,"$1.$2"); // Coloca o resto dos pontos
	}
	v=v.replace(/^(0)(\d)/g,"$2"); // Coloca hífen entre o quarto e o quinto
									// dígitos
	return z.value = v;
}

function moeda_decimal(z){
	v = z.value;
	v = v.replace(/\D/g,""); // Remove tudo o que não é dígito
	while(v.length<3) v = "0"+String(v);
	v = v.replace(/(\d{2})$/,",$1"); // Coloca a virgula
	v = v.replace(/(\d+)(\d{3},\d{2})$/g,"$1.$2"); // Coloca o primeiro ponto

	var qtdLoop = (v.length-3)/3;
	var count = 0;

	while (qtdLoop > count) {
		count++;
		v = v.replace(/(\d+)(\d{3}.*)/,"$1.$2"); // Coloca o resto dos pontos
	}
	v=v.replace(/^(0)(\d)/g,"$2"); // Coloca hífen entre o quarto e o quinto
									// dígitos
	return z.value = v;
}

function moeda_decimal2(z){	
	var v = z.toFixed(2).toString();
	v = v.replace(/\D/g,""); // Remove tudo o que não é dígito
	while(v.length<3) v = "0"+String(v);
	v = v.replace(/(\d{2})$/,",$1"); // Coloca a virgula
	v = v.replace(/(\d+)(\d{3},\d{2})$/g,"$1.$2"); // Coloca o primeiro ponto

	var qtdLoop = (v.length-3)/3;
	var count = 0;

	while (qtdLoop > count) {
		count++;
		v = v.replace(/(\d+)(\d{3}.*)/,"$1.$2"); // Coloca o resto dos pontos
	}
	v=v.replace(/^(0)(\d)/g,"$2"); // Coloca hífen entre o quarto e o quinto
									// dígitos
	return z.value = v;
}


function asFloat(str) {
	str = str.replace('.','');
	str = str.replace(',','.');
	var ret = parseFloat(str);
	return (isNaN(ret)?0:ret);
}

function just_number(z){
	z.value=z.value.replace(/\D/g,'');
}

function just_letters(z){
	z.value=z.value.replace(/\d/g,'');
}

function just_letters_without_special(z){
	z.value=z.value.replace(/\d/g,'');
	z.value=z.value.replace(/[`~!@#$%^&*()_|+\-=?;:",.<>\{\}\[\]\\\/]/gi, '');
}

function formata_numeros(z){	
	var v = z.toFixed(0).toString();
	v = v.replace(/\D/g,""); // Remove tudo o que não é dígito
	v = v.replace(/(\d+)(\d{3},\d{2})$/g,"$1.$2"); // Coloca o primeiro ponto

	var qtdLoop = (v.length-3)/3;
	var count = 0;

	while (qtdLoop > count) {
		count++;
		v = v.replace(/(\d+)(\d{3}.*)/,"$1.$2"); // Coloca o resto dos pontos
	}	
	return z.value = v;
}

// configura campos com a classe "data" para terem o calendário do jquery
function setup_datepicker() {
	if (jQuery('.data').length > 0) {
		jQuery('.data').each(function(){
			if(!jQuery(this).hasClass('binded')){
				jQuery(this).datepicker({
					dateFormat: 'dd/mm/yy',
					showOn : 'button',
					buttonImage : baseUrl + 'img/calendar.gif',
					buttonImageOnly : true,
					buttonText : 'Escolha uma data',
					dayNames : ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sabado'],
					dayNamesShort : ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
					dayNamesMin : ['D','S','T','Q','Q','S','S'],
					monthNames : ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
					monthNamesShort : ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
				}).mask("99/99/9999").addClass('binded');
			}
		});
	}

	if (jQuery('.datahora').length > 0) {
		jQuery('.datahora').each(function(){
			if(!jQuery(this).hasClass('binded')){
				jQuery(this).datetimepicker({
					timeOnlyTitle: 'Escolha a hora',
					timeText: 'Hora',
					hourText: 'Horas',
					minuteText: 'Minutos',
					secondText: 'Segundos',
					millisecText: 'Milissegundos',
					timezoneText: 'Fuso horário',
					currentText: 'Agora',
					closeText: 'Fechar',
					dayNames : ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sabado'],
					dayNamesShort : ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
					dayNamesMin : ['D','S','T','Q','Q','S','S'],
					monthNames : ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
					monthNamesShort : ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
					timeFormat: 'hh:mm',
					dateFormat: 'dd/mm/yy ',
					amNames: ['a.m.', 'AM', 'A'],
					pmNames: ['p.m.', 'PM', 'P'],
					ampm: false
				}).mask("99/99/9999 99:99").addClass('binded');
			}
		});
	}
}

//configura campos com a classe "data" para terem o calendário do jquery
function setup_datepicker_limit(antes, depois) {
	if (jQuery('.data').length > 0) {
		jQuery('.data').each(function(){
			if(!jQuery(this).hasClass('binded')){
					
				//{minDate: "-14D", maxDate: "+1M"}
				
				
				jQuery(this).datepicker({
					minDate: "-"+antes+"D",
					maxDate: "+1" + depois,
					dateFormat: 'dd/mm/yy',
					showOn : 'button',
					buttonImage : baseUrl + 'img/calendar.gif',
					buttonImageOnly : true,
					buttonText : 'Escolha uma data',
					dayNames : ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sabado'],
					dayNamesShort : ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
					dayNamesMin : ['D','S','T','Q','Q','S','S'],
					monthNames : ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
					monthNamesShort : ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
				}).mask("99/99/9999").addClass('binded');
			}
		});
	}

	if (jQuery('.datahora').length > 0) {
		jQuery('.datahora').each(function(){
			if(!jQuery(this).hasClass('binded')){
				jQuery(this).datetimepicker({
					timeOnlyTitle: 'Escolha a hora',
					timeText: 'Hora',
					hourText: 'Horas',
					minuteText: 'Minutos',
					secondText: 'Segundos',
					millisecText: 'Milissegundos',
					timezoneText: 'Fuso horário',
					currentText: 'Agora',
					closeText: 'Fechar',
					dayNames : ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sabado'],
					dayNamesShort : ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
					dayNamesMin : ['D','S','T','Q','Q','S','S'],
					monthNames : ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
					monthNamesShort : ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
					timeFormat: 'hh:mm',
					dateFormat: 'dd/mm/yy ',
					amNames: ['a.m.', 'AM', 'A'],
					pmNames: ['p.m.', 'PM', 'P'],
					ampm: false
				}).mask("99/99/9999 99:99").addClass('binded');
			}
		});
	}
}

function setup_date() {
	jQuery('.data').mask("99/99/9999");
}

function setup_time() {
	jQuery('.hora:not(.binded)').mask("99:99").addClass('binded');
}

function atualizaListaLogIntegracaoOutbox(div) {
	var div = jQuery(div);
	bloquearDiv(div);
	div.load(baseUrl + "logs_integracoes/listagem_outbox/" + Math.random());
}

function atualizaListaConfiguracaoComissao(div) {
	var div = jQuery(div);
	bloquearDiv(div);
	div.load(baseUrl + "configuracao_comissoes/listagem/" + Math.random());
}

function atualizaListaConfiguracaoComissaoCorretora(div) {
	var div = jQuery(div);
	bloquearDiv(div);
	div.load(baseUrl + "configuracao_comissoes/listagem_por_corretora/" + Math.random());
}

function atualizarChecklistVeiculo(div) {
	var div = jQuery(div);
	bloquearDiv(div);
	div.load(baseUrl + "Veiculos/dados_checklist/" + Math.random());
}

function carregarCockpitRMA(div) {
	var div = jQuery(div);
	bloquearDiv(div);
	div.load(baseUrl + "Profissionais/cockpit_rma/" + Math.random());
}

function carregarCockpitTELECONSULT(div) {
	var div = jQuery(div);
	bloquearDiv(div);
	div.load(baseUrl + "Profissionais/cockpit_teleconsult/" + Math.random());
}

function carregarCockpitEmbaTran(div){
	var div = jQuery(div);
	bloquearDiv(div);
	div.load(baseUrl + "Profissionais/cockpit_embarcador_transportador/" + Math.random());
}

function carregarCockpitSinistro(div){
	var div = jQuery(div);
	bloquearDiv(div);
	div.load(baseUrl + "Profissionais/cockpit_sinistro/" + Math.random());
}

function carregarCockpitOrigemDestino(div){
	var div = jQuery(div);
	bloquearDiv(div);
	div.load(baseUrl + "Profissionais/cockpit_origem_destino/" + Math.random());
}

function atualizaListaOperadores(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "operadores/listagem/" + Math.random());
}

function atualizaListaLogSerasa(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "relatorio_fichas_scorecard/listagem/" + Math.random());
}

function atualizaConsultaSerasa(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "relatorio_fichas_scorecard/listagem_consulta_serasa/" + Math.random());
}

function atualizaListaLogsExclusaoVinculo(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "logs_exclusaovinculos/listagem/" + Math.random());
}

function atualizaListaProprietarios(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "proprietarios/listagem/" + Math.random());
}

function atualizaListaProfissionaisNegativados(){
	var div = jQuery("div.lista");
	bloquearDiv(div); 
	div.load(baseUrl + "profissionais_negativados/listagem/" + Math.random());
}

function atualizaListaArtigosCriminais(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "artigos_criminais/listagem/" + Math.random());
}

function atualizaListaDetalhesItensPedidos(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "detalhes_itens_pedidos/listagem/" + Math.random());
}

function atualizaListaVeiculosOcorrencias(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "veiculos_ocorrencias/listagem/" + Math.random());
}

function atualizaListaCriteriosDistribuicao(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "criterios_distribuicao/listagem/" + Math.random());
}

function atualizaListaAtualizarContratos() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_produtos_contratos/atualizacao_contratos_listagem/" + Math.random());
}

function listaIscasTecnologia(codigo_viag){
	var div = jQuery("div.isca");
	bloquearDiv(div);
	div.load(baseUrl + "viagens/exibir_iscas_tecnologias/" + codigo_viag + '/' + Math.random());
}

function listaOcorrencias(codigo_viag){
	var div = jQuery("div.ocorrencia");
	bloquearDiv(div);
	div.load(baseUrl + "ocorrencias/viagem_ocorrencias/" + codigo_viag + '/' + Math.random());
}

function listaEmbarcadorTransportador(codigo_cliente){
	var div = jQuery("div.embarcador_transportador");
	bloquearDiv(div);
	div.load(baseUrl + "embarcadores_transportadores/listar_assinaturas/" + codigo_cliente + '/' + Math.random());
}

function listaMatrizFilial(codigo_cliente){
	var div = jQuery("div.matriz_filial");
	bloquearDiv(div);
	div.load(baseUrl + "matrizes_filiais/listar_assinaturas/" + codigo_cliente + '/' + Math.random());
}

function atualizaViagensOperador() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "Operadores/viagens_operadores_listagem/" + Math.random());
}

function atualizaListaItinerarios() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "Viagens/itinerario_listagem/" + Math.random());
}

function atualizaListaLogsIntegracoes() {
	var div = jQuery("div#logs_integracoes");
	bloquearDiv(div);
	div.load(baseUrl + "LogsIntegracoes/listagem/" + Math.random());
}

function atualizaListaLogsAplicacoes() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "LogsAplicacoes/listagem/" + Math.random());
}
function atualizaListaConsultaFichasScorecard() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "fichas_scorecard/listagem_fichas_scorecard/" + Math.random());
}

function atualizaListaLogConsultas() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "fichas_scorecard/listagemLogConsultas/" + Math.random());
}

function atualizaListaWebsmRetorno() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "WebsmRetornos/listagem/" + Math.random());
}

function atualizaListaFaturamentoPorCliente() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/clientes/faturamento_por_cliente_listagem/" + Math.random());
}

function atualizaListaItinerariosSm() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/itinerarios_solicitacoes_monitoramento/por_cliente_listagem/" + Math.random());	
}
function atualizaListaBandeiras() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/Bandeiras/listagem/" + Math.random());	
}
function atualizaListaRegioes() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/Regioes/listagem/" + Math.random());	
}
function atualizaListaTipoReferencias() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "tipos_referencias/listagem/" + Math.random());	
}
function atualizaInformacoesTecnicas() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes/informacoes_tecnicas/" + Math.random());
}
function atualizaViagemEscoltas(viag_codigo) {
	var div = jQuery("div.viagem_escoltas");
	bloquearDiv(div);
	div.load(baseUrl + "viagens/listar_escoltas/" + viag_codigo + "/" + Math.random());
}
function atualizaClienteGerenciadoras(cliente_codigo) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes/listar_gerenciadoras/" + cliente_codigo + "/" + Math.random());
}
function inicioViagem() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "viagens/add_viagem/" + Math.random());
}
function checklistViagemAnalitico() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "viagens/checklist_analitico_listagem/" + Math.random());
}
function checklistViagemSintetico() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "viagens/checklist_sintetico_listagem/" + Math.random());
}
function atualizaListaEmbarcadoresTransportadores() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/embarcadores_transportadores/listagem/" + Math.random());	
}
function atualizaListaMatrizesFiliais() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/matrizes_filiais/listagem/" + Math.random());	
}
function atualizaListaPagadorPreco() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/embarcadores_transportadores/listagem_pagador_preco/" + Math.random());	
}
function atualizaListaHistoricosSms(codigo) {
	var div = jQuery("div.listagem");
	bloquearDiv(div);
	div.load(baseUrl + "/historicos_sms/listagem/" + codigo + '/' + Math.random());
}

function atualizaListaLogsAtendimento(){
	var div = jQuery("div.lista");
	bloquearDiv(div);  
	div.load(baseUrl + "logs_atendimento/listagem/"  + Math.random());
}
function atualizaListaAtendimentosSms() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/atendimentos_sms/listagem/" + Math.random());
}

function atualizaListaAtendimentosSmsConsulta() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/atendimentos_sms/listagem_consulta/" + Math.random());
}

function atualizaListaDuracaoSM() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "solicitacoes_monitoramento/estatistica_duracao_sm_listagem/" + Math.random());
}

function atualizaListaSolicitacoesMonitoramento() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/solicitacoes_monitoramento/listagem/" + Math.random());
}

function atualizaListaSituacaoMonitoramento() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/solicitacoes_monitoramento/situacao_monitoramento_grafico/" + Math.random());
}

function atualizaListaFaixasValores() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "faixas_valores/listagem/" + Math.random());
}

function atualizaListaRankingFaturamento() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "notas_fiscais/ranking_faturamento_listagem/" + Math.random());
}

function atualizaListaRankingCorretora() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "notas_fiscais/ranking_corretora_listagem/" + Math.random());
}

function atualizaListaRankingSeguradora() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "notas_fiscais/ranking_seguradora_listagem/" + Math.random());
}

function atualizaListaRankingGestores() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "notas_fiscais/ranking_gestores_listagem/" + Math.random());
}

function atualizaListaNotasFiscaisPorBanco(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "notas_fiscais/por_banco_listagem/" + Math.random());
}

function atualizaListaClientesProdutosServicos() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_produtos_servicos/listagem/" + Math.random());
}

function atualizaListaClientesDuplicados() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes/listar_clientes_duplicados/" + Math.random());
}


function atualizaListaConsultaProfissionalSegundaVia() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "fichas/listagem_segunda_via_profissional/" + Math.random());
}

function atualizarListaEmailsFinanceiros(codigo_cliente){
	div = jQuery('div.lista');
	bloquearDiv(div);
	div.load(baseUrl + 'clientes/listar_emails_financeiros/' + codigo_cliente + '/' + Math.random() );
}

function atualizaListaSolicitacoesMonitoramentoHistorico() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/solicitacoes_monitoramento/listagem_historico/" + Math.random());
}

function atualizaListaRepresentantes() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/representantes/listagem/" + Math.random());
}

function atualizaListaEventos() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/eventos_viagem/listagem/" + Math.random());
}

function atualizaListaAnaliticoSinistros() {
	var div = jQuery("div.lista");
	bloquearDiv(div); 
	div.load(baseUrl + "/sinistros/listagem_sinistros_analitico/" + Math.random());
}

function atualizaListaSinteticoSinistros() {
	var div = jQuery("div.lista");
	bloquearDiv(div); 
	div.load(baseUrl + "/sinistros/listagem_sinistros_sintetico/" + Math.random());
}

function atualizaConsultaDesconto() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/clientes_produtos_descontos/consulta_listagem/" + Math.random());
}

function atualizaListaDemostrativoCT(){
   var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/relatorio_fichas_scorecard/listagem_ct/" + Math.random());
}

function atualizaListaOperacoes() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/operacoes/listagem_exames/" + Math.random());
}

function atualizaListaClientesExames(destino) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "exames/listagem_exames/" + destino.toLowerCase() + "/" + Math.random());
}

function atualizaListaClientesInformacoes(destino) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "exames/listagem_informacoes/" + destino.toLowerCase() + "/" + Math.random());
}

function atualizaListaClientes(destino) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes/listagem/" + destino.toLowerCase() + "/" + Math.random());
}

function atualizaListaClientesPpp(destino) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes/listagem/" + destino.toLowerCase() + "/ppp/" + Math.random());
}

function atualizaListaClientesImplantacao() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_implantacao/listagem/" + Math.random());
}

function atualizaListaEstatisticaSmSintetico() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "solicitacoes_monitoramento/listagem_estatisticas_sm_sintetico/" + Math.random());
}


function atualizaListaClientesProdutosContratosSemContatros(destino) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_produtos_contratos/listagemContratos/" + destino.toLowerCase() + "/" + Math.random());
}

function atualizaListaMensagensDeAcessos() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/mensagens_de_acessos/listagem/" + Math.random());
}

function atualizaListaRotas() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "Rotas/listagem/" + Math.random());
}

function atualizaFichaForense() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/fichas/forense_listagem/" + Math.random());
}

function atualizaLiberarFichaForense() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/fichas/liberar_fichas_forense_listagem/" + Math.random());
}

function atualizaListaTAatuAreaAtuacao() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/areas_atuacoes/estatistica_distribuidor_automatico_listagem/" + Math.random());
}

function atualizaListaClientesVisualizar(destino, input_id) {
	var div = jQuery("div#lista-clientes-visualizar");
	bloquearDiv(div);
	if (input_id == null)
		div.load(baseUrl + "clientes/listagem_visualizar/" + destino.toLowerCase() + "/" + Math.random());
	else
		div.load(baseUrl + "clientes/listagem_visualizar/" + destino.toLowerCase() + "/searcher:" + input_id + "/" + Math.random());
}

function atualizaListaFornecedoresVisualizar(destino, input_id, input_display) {
	var div = jQuery("div#lista-fornecedores-visualizar");
	bloquearDiv(div);
	if (input_id == null)
		div.load(baseUrl + "fornecedores/listagem_visualizar/" + destino.toLowerCase() + "/" + Math.random());
	else
		div.load(baseUrl + "fornecedores/listagem_visualizar/" + destino.toLowerCase() + "/searcher:" + input_id + "/display:" + input_display + "/" + Math.random());
}

function atualizaListaCredenciadosVisualizar(destino, input_id, input_display) {
	var div = jQuery("div#lista-credenciados-visualizar");
	bloquearDiv(div);
	if (input_id == null)
		div.load(baseUrl + "propostas_credenciamento/listagem_visualizar/" + destino.toLowerCase() + "/" + Math.random());
	else
		div.load(baseUrl + "propostas_credenciamento/listagem_visualizar/" + destino.toLowerCase() + "/searcher:" + input_id + "/display:" + input_display + "/" + Math.random());
}

function atualizaListaCeps(input_id) {
	var div = jQuery("div#lista-cep");
	bloquearDiv(div);
	div.load(baseUrl + "enderecos/listagem_cep/searcher:" + input_id + "/" + Math.random());
}

function atualizaListaReferenciasVisualizar(destino, input_id, input_display) {
	var div = jQuery("div#lista-referencias-visualizar");
	bloquearDiv(div);
	div.load(baseUrl + "referencias/listagem_visualizar/" + destino.toLowerCase() + "/searcher:" + input_id + "/display:" + input_display + "/" + Math.random());
}
function atualizaListaEscoltaVisualizar(input_id, input_display) {
	var div = jQuery("div#lista-escoltas-visualizar");
	bloquearDiv(div);
	div.load(baseUrl + "escoltas/listagem/" + "/searcher:" + input_id + "/display:" + input_display + "/" + Math.random());
}

function atualizaListaRotasVisualizar(input_id, input_display) {
	var div = jQuery("div#lista-rotas-visualizar");
	bloquearDiv(div);
	div.load(baseUrl + "rotas/listagem_visualizar/searcher:" + input_id + "/display:" + input_display + "/" + Math.random());
}

function atualizaListaClienteCobrador() {
	var div = jQuery("div.lista_cliente_cobrador");
	bloquearDiv(div);
	div.load(baseUrl + "/clientes/listagem_cliente_cobrador/0/" + Math.random());
}

function atualizaListaRelatorioFaturamento() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/notafis/listagem/" + Math.random());
}

function atualizaListaClientesCadastrados() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/clientes/listagem_clientes_cadastrados/" + Math.random());
}

function atualizaListaClientesDataCadastro() {
	var div = jQuery("div.lista_clientes");
	bloquearDiv(div);
	div.load(baseUrl + "/clientes/listagem_clientes_data_cadastro/0/" + Math.random());
}

function atualizaListaProdutosClientesDataCadastro(codigo_cliente) {
	var div = jQuery("div.lista_produtos");
	bloquearDiv(div);
	div.load(baseUrl + "/clientes_produtos/listagem_data_cadastro/" + codigo_cliente + '/' + Math.random());
}

function atualizaListaContatosClientesDataCadastro(codigo_cliente) {
	var div = jQuery("div.lista_contatos");
	bloquearDiv(div);
	div.load(baseUrl + "/clientes_contatos/listagem_data_cadastro/" + codigo_cliente + '/' + Math.random());
}

function atualizaListaEnderecosClientesDataCadastro(codigo_cliente) {
	var div = jQuery("div.lista_enderecos");
	bloquearDiv(div);
	div.load(baseUrl + "/clientes_enderecos/listagem_data_cadastro/" + codigo_cliente + '/' + Math.random());
}

function atualizaListaClientesLog() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_log/listagem/0/" + Math.random());
}

function atualizaListaEnderecoClientesLog() {
	var div = jQuery("div.lista_endereco");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_enderecos_log/listagem/0/" + Math.random());
}

function atualizaListaContatoClientesLog() {
	var div = jQuery("div.lista_contato");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_contatos_log/listagem/0/" + Math.random());
}

function atualizaListaProdutos() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "produtos/listagem/" + Math.random());
}

function atualizaListaServicos() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "servicos/listagem/" + Math.random());
}

function atualizaListaMercadorias() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "mercadorias/listagem/" + Math.random());
}

function atualizaListaTipoTransporte() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "tipo_transportes/listagem/" + Math.random()) ;
}

function atualizaListaProdutoClientesLog() {
	var div = jQuery("div.lista_produto");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_produtos_log/listagem/0/" + Math.random());
}
function atualizaListaProdutoServicosClientesLog() {
	var div = jQuery("div.lista_produto_servico");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_produtos_servicos_log/listagem/0/" + Math.random());
}

function atualizaListaFichas(destino) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "fichas/listagem/" + destino.toLowerCase() + "/" + Math.random());
}

function atualizaLista(el, destino) {
	var div = jQuery(el);
	bloquearDiv(div);
	div.load([baseUrl, destino.toLowerCase(), Math.random()].join("/"));
}

function atualizaListaClientesProdutos(destino, codigo_cliente) {
	if (typeof(codigo_cliente) == "undefined")
		codigo_cliente = '';
	var div =jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_produtos/lista_produtos_servicos/" + destino.toLowerCase() + "/" + codigo_cliente + "/" + Math.random(), function() {
		$('tr.class-new').effect('highlight');
	});
}

function atualizaListaClientesProdutosFinanceiro(codigo_cliente) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_produtos/lista_produtos_servicos_financeiro/" + codigo_cliente + "/" + Math.random());
}

function atualizaListaClientesProdutosParaContrato() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_produtos/listagem_produtos/" + Math.random());
}

function atualizaListaLiberacoesProvisorias(destino) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	jQuery("div.lista").load(baseUrl + "liberacoes_provisorias/listagem/" + destino.toLowerCase() + "/" + Math.random());
}

function atualizaListaFornecedores(destino) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	if(destino == null){
		div.load(baseUrl + "fornecedores/listagem/"+ Math.random());
	}
	else{
		div.load(baseUrl + "fornecedores/listagem/"+ destino +"/"+ Math.random());
	}
}

function atualizaListaFornecedoresAgenda() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "fornecedores_capacidade_agenda/listagem/"+ Math.random());
}

function atualizaListaSeguradoras(destino) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "seguradoras/listagem/" + destino + "/" + Math.random());
}

function atualizaListaFiliais() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "filiais/listagem/" + Math.random());
}

function atualizaListaUsuarios() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "usuarios/listagem/" + Math.random());
}

function atualizaListaEmailsFinanceiros() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes/listar_emails_financeiros/" + Math.random());
}

function atualizaListaUsuariosPorCliente(codigo_cliente) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "usuarios/listagem_por_cliente/" + codigo_cliente + "/" + Math.random());
}

function atualizaListaFuncionariosPorCliente(codigo_cliente) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "funcionarios/listagem_por_cliente/" + codigo_cliente + "/" + Math.random());
}

function atualizaListaUsuariosAlertasPorCliente(codigo_cliente) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "usuarios/listagem_alertas_por_cliente/" + codigo_cliente + "/" + Math.random());
}

function atualizaListaUsuariosPorSeguradora(codigo_seguradora) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "usuarios/listagem_por_seguradora/" + codigo_seguradora + "/" + Math.random());
}

function atualizaListaUsuariosPorCorretora(codigo_corretora) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "usuarios/listagem_por_corretora/" + codigo_corretora + "/" + Math.random());
}

function atualizaListaUsuariosPorFornecedor(codigo_fornecedor) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	$.ajax({
        url: baseUrl + 'usuarios/listagem_por_fornecedor/' + codigo_fornecedor + '/' + Math.random(),
        beforeSend: function(){
            bloquearDiv(div);
        },
        success: function(data){
            if(data != null){
                div.html(data);
            }
        },
        error: function(erro){
	        div.unblock();
        }
    });
}

function atualizaListaUsuariosPorFilial(codigo_filial) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "usuarios/listagem_por_filial/" + codigo_filial + "/" + Math.random());
}

function atualizaListaEnderecos() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "enderecos/listagem/" + Math.random());
}

function atualizaListaClientesRepresentantes(codigo_cliente) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_representantes/listagem_representantes/" + codigo_cliente + "/" + Math.random());
}

function atualizaListaClientesProdutosContratos(codigo_cliente) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_produtos_contratos/listagem/" + codigo_cliente + "/" + Math.random());
}

function atualizaListaClientesProcuracoes(codigo_cliente) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_procuracoes/listagem/" + codigo_cliente + "/" + Math.random());
}

function atualizaListaAreasAtuacoes() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "areas_atuacoes/listagem/" + Math.random());
}
function atualizalistaCockpitMotorista(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "profissionais/listar_cockpit_motorista/" + Math.random());
}

function atualizaListaInformacoesClientes() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "informacoes_clientes/listagem/" + Math.random());
}

function atualizaListaClientesVipsTeleconsult() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "clientes/listar_clientes_vips/" + Math.random());
}

function atualizaListaFuncionarios() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "funcionarios/listagem/" + Math.random());
}

function atualizaListaEstatisticaSmAnalitico() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "solicitacoes_monitoramento/listagem_estatisticas_sm_analitico/" + Math.random());
}

function atualizaListaRelatorioSmAcompanhamentoViagensAnalitico() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_sm/listagem_acompanhamento_viagens_analitico/" + Math.random());
}

function atualizaListaRelatorioSmCustosDasViagensAnalitico() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_sm/listagem_custos_trajeto/" + Math.random());
}

function atualizaListaRelatorioSmOcorrenciaViagensAnalitico() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_sm/listagem_ocorrencia_viagens_analitico/" + Math.random());
}

function carrgaListaRelatorioSmAcompanhamentoViagensSinteticoTipoVeiculo() {
	var div = jQuery("#relatorio-tipo-veiculo");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_sm/listagem_acompanhamento_viagens_sintetico_tipo_veiculo/" + Math.random());
}

function carrgaListaRelatorioSmAcompanhamentoViagensSinteticoStatusSm() {
	var div = jQuery("#relatorio-status-sm");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_sm/listagem_acompanhamento_viagens_sintetico_status_sm/" + Math.random());
}

function carrgaListaRelatorioSmAcompanhamentoViagensSinteticoStatusAlvos() {
	var div = jQuery("#relatorio-status-alvos");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_sm/listagem_acompanhamento_viagens_sintetico_status_alvos/" + Math.random());
}

function atualizaListaRelatorioSmVeiculosSemViagem() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_sm/listagem_veiculos_sem_viagem/" + Math.random());
}

function atualizaListaRelatorioSmAcompanhamentoViagensSintetico() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_sm/listagem_acompanhamento_viagens_sintetico/" + Math.random());
}

function atualizaListaRelatorioSmVeiculosPorRegiao() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_sm/listagem_veiculos_por_regiao/" + Math.random());
}

function atualizaListaRelatorioSmVeiculosMapaGr() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "veiculos/veiculos_mapa_gr_listagem/" + Math.random());
}


function atualizaListaRelatorioSmSituacaoFrota() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_sm/listagem_situacao_frota/" + Math.random());
}

function atualizaListaRelatorioDre() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_dre/listagem/" + Math.random());
}

function atualizaListaAlertas() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "alertas/listagem/" + Math.random());
}

function atualizaListaWsConfiguracoes() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "ws_configuracoes/listagem/" + Math.random());
}

function atualizaListaAlertasTipos() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "alertas_tipos/listagem/" + Math.random());
}

function atualizaListaLoadplanSintetico() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "loadplans/sintetico_listagem/" + Math.random());
}

function atualizaListaLoadplanSmSintetico() {
	var div = jQuery("div.listaSms");
	bloquearDiv(div);
	div.load(baseUrl + "loadplans/sintetico_sm_listagem/" + Math.random());
}

function carregarViagemParaIncluirPosicao() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "veiculos/carregar_viagem_para_incluir_posicao/" + Math.random());
}

function atualizaListaViagensForaTemperatura() {
	var div = jQuery("div.lista");
	div.load(baseUrl + "viagens/listar_em_andamento_fora_temperatura/" + Math.random());
}

function atualizaListaConfiguracaoTipoProduto(pjur_pess_oras_codigo){
	var div = jQuery("div.produtos-cliente");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_produtos_sm/por_cliente/" + pjur_pess_oras_codigo + "/" + Math.random());
}

function atualizaListaConfiguracaoTecnologia(pjur_pess_oras_codigo){
	var div = jQuery("div.tecnologias-cliente");
	bloquearDiv(div);
	div.load(baseUrl + "clientes_tecnologias_sm/por_cliente/" + pjur_pess_oras_codigo + "/" + Math.random());
}

function atualizaListaRegrasAceiteSm(codigo_cliente_pjur){
	var div = jQuery("div.regras-aceite-sm");
	bloquearDiv(div);
	div.load(baseUrl + "regras_aceite_sm/listar/" + codigo_cliente_pjur + "/" + Math.random());
}

function atualizaListaUsuarioVeiculoAlerta(codigo_usuario){
	var div = jQuery("div.veiculos");
	bloquearDiv(div);
	div.load(baseUrl + "usuarios/listar_veiculo_alerta/" + codigo_usuario + "/" + Math.random());
}

function atualizaListaEstatisticasViagens(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "estatisticas_viagens/por_agrupamento_listagem/" + Math.random());
}

function atualizaListaRelatorioSmStatusProfissional() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "relatorios_sm_teleconsult/listagem_sms_status_profissional/" + Math.random());
}


function flashMessage(mensagem, tipo) {
	jQuery('div.message').empty().removeAttr('style').html('<div class="alert '+'alert-'+tipo+'">'+mensagem+'</div>').delay(4000).animate({opacity:0,height:0,margin:0},function(){jQuery(this).slideUp()})
}

function marcarTodos(div) {
	jQuery("div#" + div).find("input[type=checkbox]").prop("checked", true);
}

function desmarcarTodos(div) {
	jQuery("div#" + div).find("input[type=checkbox]").prop("checked", false);
}

if (!Array.prototype.filter)
{
	Array.prototype.filter = function(fun /* , thisp */)
	{
		"use strict";

		if (this == null)
			throw new TypeError();

		var t = Object(this);
		var len = t.length >>> 0;
		if (typeof fun != "function")
			throw new TypeError();

		var res = [];
		var thisp = arguments[1];
		for (var i = 0; i < len; i++)
		{
			if (i in t)
			{
				var val = t[i]; // in case fun mutates this
				if (fun.call(thisp, val, i, t))
					res.push(val);
			}
		}

		return res;
	};
}

function hc_desmarcar_todos(grafico) {
	var qtd_series = grafico.series.length;
	var i;
	for (i = 0; i<qtd_series; i++)
		grafico.series[i].hide();
}

function hc_marcar_todos(grafico) {
	var qtd_series = grafico.series.length;
	var i;
	for (i = 0; i<qtd_series; i++)
		grafico.series[i].show();
}

String.prototype.replaceAll = function(de, para){
	var str = this;
	var pos = str.indexOf(de);
	while (pos > -1){
		str = str.replace(de, para);
		pos = str.indexOf(de);
	}
	return (str);
}

function window_sizes() {
	var winW = 630, winH = 460;
	if (document.body && document.body.offsetWidth) {
	 winW = document.body.offsetWidth;
	 winH = document.body.offsetHeight;
	}
	if (document.compatMode=='CSS1Compat' &&
		document.documentElement &&
		document.documentElement.offsetWidth ) {
	 winW = document.documentElement.offsetWidth;
	 winH = document.documentElement.offsetHeight;
	}
	if (window.innerWidth && window.innerHeight) {
	 winW = window.innerWidth;
	 winH = window.innerHeight;
	}
	var janela = new Object;
	janela.width = winW;
	janela.height = winH;
	return janela;
}

function str_pad (input, pad_length, pad_string, pad_type) {
	// Returns input string padded on the left or right to specified length with
	// pad_string
	//
	// version: 1009.2513
	// discuss at: http://phpjs.org/functions/str_pad
	// + original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// + namespaced by: Michael White (http://getsprink.com)
	// + input by: Marco van Oort
	// + bugfixed by: Brett Zamir (http://brett-zamir.me)
	// * example 1: str_pad('Kevin van Zonneveld', 30, '-=', 'STR_PAD_LEFT');
	// * returns 1: '-=-=-=-=-=-Kevin van Zonneveld'
	// * example 2: str_pad('Kevin van Zonneveld', 30, '-', 'STR_PAD_BOTH');
	// * returns 2: '------Kevin van Zonneveld-----'
	var half = '', pad_to_go;
 
	var str_pad_repeater = function (s, len) {
		var collect = '', i;
 
		while (collect.length < len) {collect += s;}		 collect = collect.substr(0,len);		   return collect;	 };	   input += '';	 pad_string = pad_string !== undefined ? pad_string : ' ';		  if (pad_type != 'STR_PAD_LEFT' && pad_type != 'STR_PAD_RIGHT' && pad_type != 'STR_PAD_BOTH') { pad_type = 'STR_PAD_RIGHT'; }	 if ((pad_to_go = pad_length - input.length) > 0) {
		if (pad_type == 'STR_PAD_LEFT') { input = str_pad_repeater(pad_string, pad_to_go) + input; }
		else if (pad_type == 'STR_PAD_RIGHT') { input = input + str_pad_repeater(pad_string, pad_to_go); }
		else if (pad_type == 'STR_PAD_BOTH') {
			half = str_pad_repeater(pad_string, Math.ceil(pad_to_go/2));
			input = half + input + half;
			input = input.substr(0, pad_length);
		}
	}
 
	return input;
}

function mapa_coordenadas(lat, lon, placa) {
	if(placa !== undefined) {
		open_coord_map_dialog(
			'Vizualização no mapa',
			{ marker_title: 'PLACA '+placa, latitude: lat, longitude: lon },
			700,
			420,
			{}
		);
	} else {
		open_coord_map_dialog(
			'Vizualização no mapa',
			{ latitude: lat, longitude: lon },
			700,
			420,
			{}
		);
	}
}

function ocorrencia_veiculo_checklist( ovei_codigo ) { 
    $(".alert-error").remove();
    var result = false;
    jQuery.ajax({
        "url": baseUrl + "veiculos_ocorrencias/verificar_permissao_ocorrencia/"+ovei_codigo+"/"+Math.random(),
        "async": false,
        "success": function(data){
            data = $.parseJSON(data);
            if((data === true) || data == null){
                result = true;
            }else{
                $(".veiculos-ocorrencias").prepend("<div class=\"alert alert-error\">Esta ocorrência já está sendo analisada pelo usuário " +data+"</div>");
            }

        }
    });
    if (result) {

		$.ajax({
	        "url": baseUrl + 'veiculos_ocorrencias/tratar_veiculo_ocorrencia_checklist/' + ovei_codigo + '/' + Math.random(),
	        "async": false,
	        "success": function(data){
	            data = $.parseJSON(data);
	            if(data.status){
	            	var janela = window_sizes();
	            	var url = baseUrl + 'veiculos/' + data.action + '/';
	            	if(data.action == "incluir_checklist"){
	            		url += data.veic_placa + '/' + data.ovei_codigo  + '/' + data.checklist_por_placa + '/' + data.codigo_cliente;
	            	}
					window.open(url, 'CheckList', 'scrollbars=yes,menubar=no,height='+(janela.height-200)+',width='+(janela.width-80)+',resizable=yes,toolbar=no,status=no');
	            }
	            atualizaListaVeiculosOcorrencias2();
	        }
	    });
	} else {
		return false;
	}
}

function consulta_sm( codigo_sm ) {   
	var form = document.createElement("form");
	var form_id = ('formresult' + Math.random()).replace('.','');
	form.setAttribute("method", "post");
	form.setAttribute("target", form_id);
	form.setAttribute("action", "/portal/viagens/consulta_sm2/1");
	field = document.createElement("input");
	field.setAttribute("name", "data[QViagViagem][viag_codigo_sm]");
	field.setAttribute("value", codigo_sm);
	field.setAttribute("type", "hidden");
	form.appendChild(field);
	document.body.appendChild(form);
	var janela = window_sizes();
	window.open('', form_id, 'scrollbars=yes,menubar=no,height='+(janela.height-200)+',width='+(janela.width-80)+',resizable=yes,toolbar=no,status=no');
	form.submit();
}
function consulta_sinistro(codigo_sinistro){
	var form = document.createElement("form");
	var form_id = ('formresult' + Math.random()).replace('.','');
	form.setAttribute("method", "post");
	form.setAttribute("target", form_id);
	form.setAttribute("action", "/portal/sinistros/visualizar_sinistros/"+codigo_sinistro+"/1");
	document.body.appendChild(form);
	var janela = window_sizes();
	window.open('', form_id, 'scrollbars=yes,menubar=no,height='+(janela.height-200)+',width='+(janela.width-80)+',resizable=yes,toolbar=no,status=no');
	form.submit();
}
function consulta_loadplan( codigo_loadplan ) {   
	var form = document.createElement("form");
	var form_id = ('formresult' + Math.random()).replace('.','');
	form.setAttribute("method", "post");
	form.setAttribute("target", form_id);
	form.setAttribute("action", "/portal/logs_integracoes/outbox/1");
	field = document.createElement("input");
	field.setAttribute("name", "data[LogIntegracaoOutbox][loadplan]");
	field.setAttribute("value", codigo_loadplan);
	field.setAttribute("type", "hidden");
	form.appendChild(field);
	field = document.createElement("input");
	field.setAttribute("name", "data[LogIntegracaoOutbox][data_inicial]");
	field.setAttribute("value", "");
	field.setAttribute("type", "hidden");
	form.appendChild(field);
	field = document.createElement("input");
	field.setAttribute("name", "data[LogIntegracaoOutbox][data_final]");
	field.setAttribute("value", "");
	field.setAttribute("type", "hidden");
	form.appendChild(field);
	document.body.appendChild(form);
	var janela = window_sizes();
	window.open('', form_id, 'scrollbars=yes,menubar=no,height='+(janela.height-200)+',width='+(janela.width-80)+',resizable=yes,toolbar=no,status=no');
	form.submit();
}

function consulta_sm_por_tipo_transporte(tipo_transporte,data_inicial,data_final,codigo_cliente) {   
	var form = document.createElement("form");
	var form_id = ('formresult' + Math.random()).replace('.','');
	form.setAttribute("method", "post");
	form.setAttribute("target", '_blank');
	form.setAttribute("action", "/portal/relatorios_sm/acompanhamento_viagens_analitico/"+Math.random());
	field = document.createElement("input");
	field.setAttribute("name", "data[RelatorioSm][codigo_tipo_transporte][]");
	field.setAttribute("value", tipo_transporte);
	field.setAttribute("type", "hidden");
	form.appendChild(field);
	field = document.createElement("input");
	field.setAttribute("name", "data[RelatorioSm][data_inicial]");
	field.setAttribute("value", data_inicial);
	field.setAttribute("type", "hidden");
	form.appendChild(field);
	field = document.createElement("input");
	field.setAttribute("name", "data[RelatorioSm][data_final]");
	field.setAttribute("value", data_final);
	field.setAttribute("type", "hidden");
	form.appendChild(field);
	field = document.createElement("input");
	field.setAttribute("name", "data[RelatorioSm][codigo_cliente]");
	field.setAttribute("value", codigo_cliente);
	field.setAttribute("type", "hidden");
	form.appendChild(field);
	for(var i = 2; i < 8; i++){
		field = document.createElement("input");
		field.setAttribute("name", "data[RelatorioSm][codigo_status_viagem][]");
		field.setAttribute("value", i);
		field.setAttribute("type", "hidden");
		form.appendChild(field);
	}
	document.body.appendChild(form);
	form.submit();
}

function consulta_ficha_scorecard( codigo_ficha_scorecard ) {   
	var form = document.createElement("form");
	var form_id = ('formresult' + Math.random()).replace('.','');
	form.setAttribute("method", "post");
	form.setAttribute("target", form_id);
	form.setAttribute("action", "/portal/fichas_status_criterios/resultado_ficha/"+codigo_ficha_scorecard+"/1");
	document.body.appendChild(form);
	var janela = window_sizes();
	window.open('', form_id, 'scrollbars=yes,menubar=no,height='+(janela.height-200)+',width='+(janela.width-80)+',resizable=yes,toolbar=no,status=no');
	form.submit();
}

function tempo_restante_sm(codigo_sm) {
	jQuery(document).ready(function() {
		$.ajax({
			url: baseUrl+"solicitacoes_monitoramento/tempo_entre_pontos/"+codigo_sm+'/'+ Math.random()
			,type: 'post'
			,dataType: 'json'
			,success: function(data) {
				if (data.pontos.atual.latitude != null)
					jQuery("#PosicaoAtual").html(helper_posicao_geografica(data.pontos.atual.latitude, data.pontos.atual.longitude, data.pontos.atual.descricao,''));
				if (data.pontos.destino.latitude != null)
					jQuery("#PosicaoDestino").html(helper_posicao_geografica(data.pontos.destino.latitude, data.pontos.destino.longitude, data.pontos.destino.descricao, ''));
				jQuery("#RestanteDistancia").html(data.distancia);
				jQuery("#RestanteTempo").html(data.tempo);
				jQuery("#tempo_restante").show();
			}
		});
	})
}

function tempo_restante_sm2(codigo_sm) {
	jQuery(document).ready(function() {
		$.ajax({
			url: baseUrl+"viagens/tempo_entre_pontos/"+codigo_sm+'/'+ Math.random()
			,type: 'post'
			,dataType: 'json'
			,success: function(data) {
				if (data.pontos.atual.latitude != null)
					jQuery("#PosicaoAtual").html(helper_posicao_geografica(data.pontos.atual.latitude, data.pontos.atual.longitude, data.pontos.atual.descricao,''));
				if (data.pontos.destino.latitude != null)
					jQuery("#PosicaoDestino").html(helper_posicao_geografica(data.pontos.destino.latitude, data.pontos.destino.longitude, data.pontos.destino.descricao, ''));
				jQuery("#RestanteDistancia").html(data.distancia);
				jQuery("#RestanteTempo").html(data.tempo);
				jQuery("#tempo_restante").show();
			}
		});
	})
}

function helper_posicao_geografica(latitude, longitude, descricao, placa) {
	return "<span title='latitude:"+latitude+" longitude:"+longitude+"'><a href='#' onclick=\"mapa_coordenadas("+latitude+","+longitude+",'"+placa+"')\">" + descricao + "</a></span>"
}

function helper_itinerario(descricao, codigo_sm) {
	return "<a href=\""+baseUrl+"solicitacoes_monitoramento/itinerario_mapa/"+codigo_sm+"\" onclick=\"return open_popup(this)\">" + descricao + "</a>"
}

function helper_codigo_sm(codigo_sm) {
	return "<a href='javascript:void(0)' onclick=\"consulta_sm('" + codigo_sm + "')\">" + codigo_sm + "</a>";
}

function helper_placa(placa, data_inicial, data_final, codigo_cliente) {
	if(codigo_cliente == "undefined")
		return "<a href='javascript:void(0)' onclick=\"eventos_logisticos_sm('" + placa + "', '" + data_inicial + "', '" + data_final + "')\">" + placa + "</a>";
	else 
		return "<a href='javascript:void(0)' onclick=\"eventos_logisticos_sm('" + placa + "', '" + data_inicial + "', '" + data_final + "', '" + codigo_cliente + "')\">" + placa + "</a>";
}

function retornaValoresComboBox( combo_box ) {

	var dados   = $(combo_box + ' option');		
	var valores = new Array();
	
	if( $(combo_box).val() == '' ) {
		for( var i = 0; i < dados.length; i++ ){			
			if( dados[i].value != "" ) {
				valores[i-1] = dados[i].value;
			}
		}
	} else {
		valores[0] = $(combo_box).val();
	}

	return valores;
}

function preencheLatitudeLongitudeReferencia(refe_codigo){
	var latitude = jQuery('.refe-latitude');
	var longitude = jQuery('.refe-longitude');
	$.ajax({
		url: baseUrl + 'referencias/busca_latitude_longitude/' + refe_codigo + '/' + Math.random(),
		type: 'post',
		dataType: 'json',
		beforeSend: function(){
			latitude.val('Aguarde, carregando...');
			longitude.val('Aguarde, carregando...');
		},
		success: function(data){
			if(data){
				latitude.val(data.refe_latitude);
				longitude.val(data.refe_longitude);
			}
		}
	});

}

function atualizaListaFichasAlterarProduto() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/fichas/listar_fichas/" + Math.random());
}

function atualizaListaEstatisticaInicioFim() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/viagens/listar_estatistica_inicio_fim/" + Math.random());
}

function atualizaListaTarefaDesenvolvimento() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/sistemas/listar_tarefas_desenvolvimento/" + Math.random());
}

function atualizaStatusObjeto(codigo,status) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	if (status == 0){
		status = 1;
	}else if(status == 1) 
		status = 0;

	div.load(baseUrl + "/objetos_acl/mudar_status/"+ codigo +'/'+ status );
}


function atualizaStatusTarefaDesenvolvimento(codigo,status) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	if (status == 1){
	status = 2
	div.load(baseUrl + "/sistemas/editar_status_tarefas_desenvolvimento/"+ codigo +'/'+ status );
	}else if(status == 2) 
	status = 1
	div.load(baseUrl + "/sistemas/editar_status_tarefas_desenvolvimento/"+ codigo +'/'+ status );
}

function atualizaStatusTarefaDesenvolvimentoDelphi(codigo,status) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	if (status == 1)
		status = 2;
	else if(status == 2) 
		status = 3;
	else if(status == 3)
		status = 1;
	div.load(baseUrl + "/sistemas/editar_status_tarefas_desenvolvimento/"+ codigo +'/'+ status );
}


function atualizaStatusMercadorias(codigo,status) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	if (status == 1){
	status = 0
	div.load(baseUrl + "/mercadorias/editar_status_mercadorias/"+ codigo +'/'+ status );
	}else if(status == 0) 
	status = 1
	div.load(baseUrl + "/mercadorias/editar_status_mercadorias/"+ codigo +'/'+ status );
}


function atualizaListaPontuacoes() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/pontuacoes_status_criterios/listar_pontuacoes/" + Math.random());
}

function atualizaListaConsultaFichasPendentes() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/fichas/listar_fichas_pendentes/" + Math.random());
}

function atualizaListaTpecas() {
	var div = jQuery("div.lista");
	bloquearDiv(div);	
	div.load(baseUrl + "tpecas/listagem/" + Math.random());
}
function atualizaListaFichasScorecard() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/fichas_scorecard/listar_fichas/" + Math.random());
}

function atualizaListaConsultaPesquisaVeiculo() {
	var div = jQuery("div.lista");
	bloquearDiv(div);	
	div.load(baseUrl + "pesquisas_veiculos/listagem/" + Math.random());
}

function atualizaListaResultadosPesquisa() { 
	var div = jQuery("div.lista");
	bloquearDiv(div);
	//alert('oi');
	div.load(baseUrl + "/fichas_scorecard/listagem_finalizadas/" + Math.random());
}

function atualizaListaResultadosPesquisaCliente() { 
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "/fichas_status_criterios/listagem_cliente/" + Math.random());
}
function atualizaListaTiposNegativacoes(){
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "tipos_negativacoes/listagem/" + Math.random());
}

function atualizaListaIps(codigo_usuario){
    var div = jQuery('div.listaIps');
    bloquearDiv(div);
    div.load(baseUrl + 'usuarios_ips/listar/' + codigo_usuario + '/' + Math.random());
}

function atualizaListaVeiculosOcorrencias2(){
    var div = jQuery('div.veiculos-ocorrencias');
    bloquearDiv(div);
    var complemento = '';
    if (($('#campo_ordem')) && ($('#campo_ordem').val()!=undefined) && ($('#campo_ordem').val()!='')) {
    	complemento = '/'+$('#campo_ordem').val();
    	complemento+= '/'+($('#direcao_ordem').val()!='' && $('#direcao_ordem').val()!=undefined ? $('#direcao_ordem').val() : 'asc');
    }

    div.load(baseUrl + 'veiculos_ocorrencias/veiculos_ocorrencias_listagem'+complemento+'/' + Math.random(),function(){
    	$('.atribuido>td').css({'background-color':'#fcf8e3'});
        $('.atribuido').hover(function(){
            $(this).find('td').css({'background-color':'#f5f5f5'});
        },function(){
            $(this).find('td').css({'background-color':'#fcf8e3'});
        });
    });
}


function carregarStatusCriterio(element_cod,element_retorno){
	if($(element_cod).val()){
		var retorno = $(element_retorno);
		
		$.ajax({
			url: baseUrl + 'StatusCriterios/lista_status/' + $(element_cod).val() + '/' + Math.random(),
			dataType: 'html',
			beforeSend: function(){
				retorno.attr('readonly',true);
				retorno.html('<option>Aguarde...</option>');
			},
			success: function(data){
				if(data)
					retorno.html(data);
			},
			complete: function(){
				retorno.attr('readonly',false);
			}
		});
		
	}
}

function blogportal(link,categoria){	
	jQuery("#blog").remove();
	var form =  '<form style="display:none;" accept-charset="utf-8" target="_blank" method="POST" id="blog" action="'+link+'">';
	form += '<input type="text" value="blogportal" name="buonnyblogportal">';
	form += '<input type="text" value="'+link+'" name="link">';
	form += '<input type="text" value="'+categoria+'" name="categoria">';
	form += '</form>';
	jQuery('body').append(form);
	jQuery("#blog").submit();
}

function validarCPF(cpf) {
     cpf = cpf.replace(/[^\d]+/g,'');
 
    if(cpf == '') return true;
 
    // Elimina CPFs invalidos conhecidos
    if (cpf.length != 11 || 
        cpf == "00000000000" || 
        cpf == "11111111111" || 
        cpf == "22222222222" || 
        cpf == "33333333333" || 
        cpf == "44444444444" || 
        cpf == "55555555555" || 
        cpf == "66666666666" || 
        cpf == "77777777777" || 
        cpf == "88888888888" || 
        cpf == "99999999999")
        return false;
     
    // Valida 1o digito
    add = 0;
    for (i=0; i < 9; i ++)
        add += parseInt(cpf.charAt(i)) * (10 - i);
    rev = 11 - (add % 11);
    if (rev == 10 || rev == 11)
        rev = 0;
    if (rev != parseInt(cpf.charAt(9)))
        return false;
     
    // Valida 2o digito
    add = 0;
    for (i = 0; i < 10; i ++)
        add += parseInt(cpf.charAt(i)) * (11 - i);
    rev = 11 - (add % 11);
    if (rev == 10 || rev == 11)
        rev = 0;
    if (rev != parseInt(cpf.charAt(10)))
        return false;
         
    return true;
    
}

function validarCNH( cnh ) {
	cnh = cnh.replace(/[^\d]+/g,''); 
    if(cnh == '' || cnh.length != 11 || parseInt(cnh.charAt(0)) != 0 ) 
		return false;
	valor  = parseInt(cnh.charAt(0));
	valor += parseInt(cnh.charAt(1));
	valor += parseInt(cnh.charAt(2));
	valor += parseInt(cnh.charAt(3));
	valor += parseInt(cnh.charAt(4));	
	return valor > 0;//true or false
}

function validarRenavam( renavam ){
	return true;//Retirar a trava de renavam temporariamente pq nao sabem a regra de validacao do renavam
	//return renavam.replace(/[^\d]+/g,'').length == 9;
}

function validarCNPJ(cnpj) {
 
    cnpj = cnpj.replace(/[^\d]+/g,'');
 
    if(cnpj == '') return true;
     
    if (cnpj.length != 14)
        return false;
 
    // Elimina CNPJs invalidos conhecidos
    if (cnpj == "00000000000000" ||
        cnpj == "11111111111111" ||
        cnpj == "22222222222222" ||
        cnpj == "33333333333333" ||
        cnpj == "44444444444444" ||
        cnpj == "55555555555555" ||
        cnpj == "66666666666666" ||
        cnpj == "77777777777777" ||
        cnpj == "88888888888888" ||
        cnpj == "99999999999999")
        return false;
         
    // Valida DVs
    tamanho = cnpj.length - 2
    numeros = cnpj.substring(0,tamanho);
    digitos = cnpj.substring(tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(0))
        return false;
         
    tamanho = tamanho + 1;
    numeros = cnpj.substring(0,tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(1))
          return false;
           
    return true;    
}

function carrgaListaTpecasSinteticoAgrupamento() {
	var div = jQuery("#relatorio-agrupamento");
	bloquearDiv(div);
	div.load(baseUrl + "tpecas/sintetico_tpecas_agrupamento_grafico/" + Math.random());
}

function carrgaListaTpecasSinteticoTotal() {
	var div = jQuery("#relatorio-total");
	bloquearDiv(div);
	div.load(baseUrl + "tpecas/sintetico_tpecas_total_grafico/" + Math.random());
}

function carrgaListaTpecasPecaAvaria() {
	var div = jQuery("#relatorio-peca-avaria");
	bloquearDiv(div);
	div.load(baseUrl + "tpecas/tpecas_avaria_grafico/" + Math.random());
}
function carrgaListaTpecasTotalAvaria() {
	var div = jQuery("#relatorio-peca-avaria");
	bloquearDiv(div);
	div.load(baseUrl + "tpecas/tpecas_total_grafico/" + Math.random());
}

function pesquisa_cliente( id_campo_codigo_cliente ){
	var element = id_campo_codigo_cliente ? id_campo_codigo_cliente : 'ClienteCodigoCliente';
	$("#"+element).blur(function(){
		codigo = $("#"+element).val();
		if(codigo){			
			$.ajax({
				url: baseUrl + "clientes/buscar/" + codigo + "/" + Math.random(),
    			cache: false,
				type: "post",
				dataType: "json",
				success: function(data){
					if(data.sucesso == true){
						$("#ClienteCodigoDocumento").val(data.dados.codigo_documento);
						$("#ClienteRazaoSocial").val(data.dados.razao_social);
					} else {
						$("#ClienteCodigoDocumento").val("");
						$("#ClienteRazaoSocial").val("");
					}
				}
			});						
		} else {
			$("#ClienteCodigoDocumento").val("");
			$("#ClienteRazaoSocial").val("");
		}	
		return false;
	});
}

function validar_campo_autocomplete(campo_codigo,campo_visual,tipo){
	var mensagem = "";
	switch(tipo){
		case 'escolta': mensagem = 'Escolta não selecionada ou não cadastrada'; break;
		case 'corretora': mensagem = 'Corretora não selecionada ou não cadastrada'; break;
		case 'referencia': mensagem = 'Alvo não selecionado ou não cadastrado'; break;
		case 'rota': mensagem = 'Rota não selecionada ou não cadastrada'; break;
		case 'cidade': mensagem = 'Cidade não selecionado ou não cadastrado'; break;
		case 'artigos_criminais': mensagem = 'Artigo Criminal não selecionado ou não cadastrado'; break;
	}
	if($(campo_visual).val() != "" && $(campo_codigo).val() == ""){
		$(campo_visual).parent().find(".campo-validacao").remove();
		if($(campo_visual).parent().find('.icon-search'))
			$(campo_visual).parent().find('.icon-search').after("<span class='campo-validacao'></span>");
		else
			$(campo_visual).after("<span class='campo-validacao'></span>");
		$(campo_visual).parent().find(".campo-validacao").css({
			'background-image':'url('+baseUrl+'/img/icon-error.png)'
		}).attr({'title':mensagem});
	}else if($(campo_visual).val() != "" && $(campo_codigo).val() != ""){
		$(campo_visual).parent().find(".campo-validacao").remove();
		if($(campo_visual).parent().find('.icon-search'))
			$(campo_visual).parent().find('.icon-search').after("<span class='campo-validacao'></span>");
		else
			$(campo_visual).after("<span class='campo-validacao'></span>");
		$(campo_visual).parent().find(".campo-validacao").css({
			'background-image':'url('+baseUrl+'/img/icon-check.png)'
		});
	}else{
		$(campo_visual).parent().find(".campo-validacao").remove();
	}
	$(campo_visual).parent().find(".campo-validacao").css({
		'width':'22px',
		'height':'24px',
		'display':'inline-block',
		'background-position':'6px 2px',
		'background-repeat':'no-repeat',
		'vertical-align':'text-top'
	});
}

function convertToHoursMins(time) {
	if (time) {
		time = Math.floor(time);
		if (time < 1) {
			return;
		}
		hours = Math.floor(time/60);
		minutes = time%60;
		return str_pad(hours, 2, '0', 'STR_PAD_LEFT') + ':' + str_pad(minutes, 2, '0', 'STR_PAD_LEFT');
	} else {
		return "";
	}
}

function init_combo_usuarios_cliente(input_target_id, input_source_id){
	jQuery(input_source_id).blur(function(){
		jQuery(input_target_id).css("color","#000"); // change font-color to black
		jQuery(input_target_id+' option:selected').text('Aguarde, carregando...');
		jQuery.ajax({
			"url": baseUrl + "usuarios/listar_clientes/" + jQuery(input_source_id).val() + "/" + Math.random(),
			"success": function(data) {
				jQuery(input_target_id).html(data).change();
				jQuery(input_target_id).css("color","#555555"); // return default font-color
			}
		});
	});
}

function consulta_fotos_checklist(codigo_sm) {
	//if (readonly==null || readonly==undefined) readonly = '';
	var newwindow = window.open("/portal/viagens/fotos_checklist_visualizar/"+codigo_sm, "_blank", "top=0,left=0,width=600,height=600,scrollbars=yes");
	if (window.focus){
    	newwindow.focus();
	}	
}

function consulta_fotos_checklist_entrada(codigo_checklist) {
	//if (readonly==null || readonly==undefined) readonly = '';
	var newwindow = window.open("/portal/viagens/fotos_checklist_entrada/"+codigo_checklist+'/1', "_blank", "top=0,left=0,width=600,height=600,scrollbars=yes");
	if (window.focus){
    	newwindow.focus();
	}	
}

function consulta_checklist_entrada( codigo_checklist ) {   
	var janela = window_sizes();
	var newwindow = window.open("/portal/viagens/consulta_checklist_entrada/"+codigo_checklist, "_blank", "scrollbars=yes,menubar=no,top=0,left=0,height="+(janela.height-200)+",width="+(janela.width-80)+",resizable=yes,toolbar=no,status=no");
	if (window.focus){
    	newwindow.focus();
	}	
}

function consulta_checklist_saida( codigo_checklist ) {   
	var janela = window_sizes();
	var newwindow = window.open("/portal/viagens/checklist_saida/"+codigo_checklist, "_blank", "scrollbars=yes,menubar=no,top=0,left=0,height="+(janela.height-200)+",width="+(janela.width-80)+",resizable=yes,toolbar=no,status=no");
	if (window.focus){
    	newwindow.focus();
	}	
}

function hc_habilitar_qtd_series(grafico , qtd) {
	var i;
	for (i = 0; i<qtd; i++)
		grafico.series[i].show();
}

function lista_servicos_produto(input_target_id, codigo_produto, codigo_corretora, codigo_seguradora){
	if (codigo_corretora==null || codigo_corretora==undefined) codigo_corretora = '';
	if (codigo_seguradora==null || codigo_seguradora==undefined) codigo_seguradora = '';
	if (input_target_id.charAt(0)!='#' && input_target_id.charAt(0)!='.') input_target_id = '#'+input_target_id;
	jQuery(input_target_id).css("color","#000"); // change font-color to black
	jQuery(input_target_id+' option:selected').text('Aguarde, carregando...');
	jQuery.ajax({
		"url": baseUrl + "listas_de_preco_produto/listar_servicos_por_produto/" + codigo_produto + "/" +codigo_corretora + "/" +codigo_seguradora + "/" + Math.random(),
		"success": function(data) {
			jQuery(input_target_id).html(data).change();
			jQuery(input_target_id).css("color","#555555"); // return default font-color
		}
	});
}


function valida_itinerario_rota(campo_rota) {
	var id = campo_rota.id;
	var id_rota = campo_rota.value;
	var form = document.getElementById(campo_rota.form.id);
	var fd = $( '#'+form.id ).serialize();
	$.ajax({
      url: "/portal/viagens/valida_itinerario_rota/"+id_rota+"/"+Math.random(),
      type: "POST",
      data: fd,
      async: false
    }).done(function( data ) {
    	retorno = data;
    });

    return eval(retorno);
}

function consulta_tipo_placa(linha,id_placa,id_tipo_veiculo,id_tecnologia) {
	if (typeof(id_placa)==='undefined') id_placa = '#RecebsmPlaca';
	if (typeof(id_tipo_veiculo)==='undefined') id_tipo_veiculo = '#RecebsmTipo';
	if (typeof(id_tecnologia)==='undefined') id_tecnologia = '#RecebsmTecnologia';

	var placa_sem_formatacao = $(linha).find(id_placa).val();
	var inputTipo = $(linha).find(id_tipo_veiculo);
	var tecnologia = $(linha).find(id_tecnologia);

	var placa = placa_sem_formatacao.replace(/_/g,'');

    if (placa.length > 6) {  
        $.ajax({
            url: baseUrl + 'veiculos/tipo_por_placa/placa:' + placa + '/' + Math.random(),
            dataType: 'json',
            beforeSend: function() {
                inputTipo.val('Aguarde...');
                tecnologia.val('Aguarde...');
            },
            success: function(data) {
                if (data){
                    inputTipo.val(data.TTveiTipoVeiculo.tvei_descricao);
         	        tecnologia.val(data.TTecnTecnologia.tecn_descricao);
                }else{
                    inputTipo.val('Não encontrado');
                    tecnologia.val('Não encontrado');
                } 
            },
            error: function() {
                inputTipo.val('Não encontrado');
                tecnologia.val('Não encontrado');
            }
        });
    }
}

function consulta_tipo_placa_express(linha,codigo_cliente) {
    var inputTipo = $(linha).find('#RecebsmTipo');
    var inputTecnologia = $(linha).find('#RecebsmTecnologia');
    var placa_sem_formatacao = $(linha).find('#RecebsmPlaca').val();
    var placa = placa_sem_formatacao.replace(/_/g,'');
  
    if (placa.length > 6) {
		bloquearDiv($('.veiculo'));
	    $.ajax({
	        url: baseUrl + 'veiculos/tipo_por_placa/placa:' + placa + '/' + Math.random(),
	        dataType: 'json',
	        beforeSend: function() {
	            inputTipo.val('Aguarde...');
	            inputTecnologia.val('Aguarde...');
	        },
	        success: function(data) {
				$('#RecebsmPlaca').parent().removeClass('error warning').find('.error-message').remove();
	            if (data){
    	        	if( data.TTveiTipoVeiculo.tvei_codigo == 1){
    	        		jQuery('#RecebsmPlaca').parent().addClass('error').append('<div class=\"help-block error-message\" style=\"padding: 0;\">O veículo não pode ser Carreta</div>');
    	        	}
    	            inputTipo.val(data.TTveiTipoVeiculo.tvei_descricao);
    	            inputTecnologia.val(data.TTecnTecnologia.tecn_descricao);

    	        } else {
    	            inputTipo.val('Não encontrado');
    	            inputTecnologia.val('');
    	        }
            	verifica_vinculo_por_cliente(placa);
            	verifica_motorista_transportador_padrao(codigo_cliente.val(),placa);
            },
            error: function() {
                inputTipo.val('Não encontrado');
                inputTecnologia.val('');
	            $('.veiculo').unblock();
            }
	    });

		if( $('#RecebsmEmbarcador').val() ){
			bloquearDiv($('.emba_tran'));
			$.ajax({
		        'url': baseUrl + 'veiculos/transportador_por_placa/' + $('#RecebsmPlaca').val() + '/'+ $('#RecebsmEmbarcador').val()  +'/' + Math.random(),
		        dataType: 'json',
		        'success': function(data) {
		            if( data ){
		            	$('#RecebsmTransportador').val( data.Cliente.codigo );
		            }
		            $('.emba_tran').unblock();
	        	}
			});
		}
	}
}

function verifica_vinculo_por_cliente( placa ){
	//Verifica se a placa tem vinculo com o cliente
    if (placa.length > 0 && placa.indexOf('_') < 0) {
		$.ajax({
	        'url': baseUrl + 'veiculos/verifica_vinculo_por_cliente/' + placa + '/'+ $('#RecebsmCodigoCliente').val()  +'/' + Math.random(),
	        dataType: 'json',
	        'success': function(data) {
				if( !data ){
					jQuery('#RecebsmPlaca').parent().addClass('warning').append('<div class=\"help-block error-message\" style=\"padding: 0;\">O veículo não está vinculado ao cliente. <br />Ao inserir essa SM o veiculo será vinculado automaticamente.</div>');
				}
	            $('.veiculo').unblock();
        	}
		});
	}
}

function verifica_motorista_transportador_padrao(codigo_cliente, placa ){
    if (placa.length > 0) {
		$.ajax({
	        'url': baseUrl + 'solicitacoes_monitoramento/verifica_motorista_transportador_padrao/'+codigo_cliente+'/'+placa+'/'+ Math.random(),
	        dataType: 'json',
	        beforeSend: function() {
	            $('#RecebsmCodigoDocumento').val('Aguarde...');
	        },
	        success: function(data) {		
	            if (data){
	            	console.log(data);    	        	
	        		$('#RecebsmCodigoDocumento').val( data.motorista );
	        		$('#RecebsmTransportador').val( data.transportador );
	        		$('#RecebsmCodigoDocumento').blur();
	        		$('#RecebsmTransportador').removeClass('error').find('.error-message').remove();
					document.getElementById("RecebsmTransportador").value = data.transportador;
				
			        if($('#RecebsmTransportador').val() && $('#RecebsmEmbarcador').val()){
						transportador = $('#RecebsmTransportador').val();
						embarcador = $('#RecebsmEmbarcador').val();
						
						if(transportador != ''){
							$.ajax({
						        'url': baseUrl + 'solicitacoes_monitoramento/verificar_cliente_pagador/' + embarcador + '/'+ transportador +'/' + Math.random(),
						        dataType: 'json',
						        success: function(data) {			
							       	if(data){
							            if(data.ClienteProduto.pendencia_financeira){
							            	$('#RecebsmTransportador').parent().addClass('error').append('<div class=\"help-block error-message\">Entrar em Contato com o Departamento Financeiro através dos telefones:<br />(11) 3443-2517.<br />(11) 3443-2587.<br />(11) 3443-2601.</div>');
							            }else if(data.ClienteProduto.pendencia_juridica){
							            	$('#RecebsmTransportador').parent().addClass('error').append('<div class=\"help-block error-message\">Entrar em Contato com o Departamento Jurídico através dos telefones:<br />(11) 5079-2572.<br/>(11) 3443-2572.');
							            }else if(data.ClienteProduto.pendencia_comercial){
							            	$('#RecebsmTransportador').parent().addClass('error').append('<div class=\"help-block error-message\">Serviço não disponível para o embarcador e transportador selecionados. Favor entrar em contato com o Departamento Comercial.');
							            }
								    }else{
								    	$('#RecebsmTransportador').parent().addClass('error').append('<div class=\"help-block error-message\">Serviço não disponível para o embarcador e transportador selecionados. Favor entrar em contato com o Departamento Comercial.');
								    }       
					        	}
							});
						}	
					}

    	        }  
	        	$('.veiculo').unblock();
            },
            error: function() {
	            $('.veiculo').unblock();
            }
		});
	}
}

function buscar_cliente_produto( codigo_cliente, element_retorno ) {
    $.ajax({
        url: baseUrl + 'clientes_produtos/lista_produtos_tlcs/' + codigo_cliente + '/' + Math.random(),
        beforeSend: function(){
            element_retorno.html('<option value=\'\'>Aguarde...</option>');
        },
        success: function(data) {
            var comboProdutos = element_retorno;
            comboProdutos.html(data);
            if (typeof codigo != 'undefined') {
                comboProdutos.val(codigo);                
            }
        }
    });
}

function atualizaListaCorretoras(destino) {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "corretoras/listagem/"+ destino +"/"+ Math.random());
}

function atualizaListaCorretorasVisualizar(destino, input_id, input_display) {
	var div = jQuery("div#lista-corretoras-visualizar");
	bloquearDiv(div);
	if (input_id == null)
		div.load(baseUrl + "corretoras/listagem_visualizar/" + destino.toLowerCase() + "/" + Math.random());
	else
		div.load(baseUrl + "corretoras/listagem_visualizar/" + destino.toLowerCase() + "/searcher:" + input_id + "/display:" + input_display + "/" + Math.random());
}

function atualizaListagemAgenda() {
	var div = jQuery("div.lista");
	bloquearDiv(div);
	div.load(baseUrl + "consultas_agendas/listagem/"+ Math.random());
}