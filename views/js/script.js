/*
 * Easypay
 *
 * @copyright Direitos autorais (c) 2023 Trigenius
 * 
 * @author Trigenius
 * 
 * Todos os direitos reservados.
 * 
 * É concedida permissão para utilizar este software de forma gratuita. No entanto, não é permitido
 * modificar, derivar obras de, distribuir, sublicenciar e/ou vender cópias do software.
 * 
 * O SOFTWARE É FORNECIDO "COMO ESTÁ", SEM GARANTIA DE QUALQUER TIPO, EXPRESSA OU IMPLÍCITA,
 * INCLUINDO MAS NÃO SE LIMITANDO A GARANTIAS DE COMERCIALIZAÇÃO, ADEQUAÇÃO A UM PROPÓSITO ESPECÍFICO
 * E NÃO VIOLAÇÃO. EM NENHUM CASO OS AUTORES OU TITULARES DOS DIREITOS AUTORAIS SERÃO RESPONSÁVEIS
 * POR QUALQUER RECLAMAÇÃO, DANOS OU OUTRAS RESPONSABILIDADES, SEJA EM UMA AÇÃO DE CONTRATO, DELITO
 * OU QUALQUER OUTRO MOTIVO, QUE SURJA DE, FORA DE OU EM RELAÇÃO COM O SOFTWARE OU O USO OU OUTRAS
 * NEGOCIAÇÕES NO SOFTWARE.
 */


$('#guardar-pagamento-cc').on('click', function(){
		$('#guardar-pagamento-form').toggleClass('ep-d-none');
		$('#botao-guardar-pagamento-cc').toggleClass('ep-d-none');
		$('#no-guardar-pagamento-cc-div').toggleClass('ep-d-none');
		$('#cb-guardar-cc').click(); 
	});

$('#no-guardar-pagamento-cc').on('click', function(){
		$('#guardar-pagamento-form').toggleClass('ep-d-none');
		$('#botao-guardar-pagamento-cc').toggleClass('ep-d-none');
		$('#no-guardar-pagamento-cc-div').toggleClass('ep-d-none');
		$('#cb-guardar-cc').click(); 
	});



$('#guardar-pagamento-mb').on('click', function(){
		$('#guardar-pagamento-form-mb').toggleClass('ep-d-none');
		$('#botao-guardar-pagamento-mb').toggleClass('ep-d-none');
		$('#no-guardar-pagamento-mb-div').toggleClass('ep-d-none');
		$('#cb-guardar-mb').click(); 
	});

$('#no-guardar-pagamento-mb').on('click', function(){
		$('#guardar-pagamento-form-mb').toggleClass('ep-d-none');
		$('#botao-guardar-pagamento-mb').toggleClass('ep-d-none');
		$('#no-guardar-pagamento-mb-div').toggleClass('ep-d-none');
		$('#cb-guardar-mb').click(); 
	});


$('#guardar_mbway').on('click', function(){
	$('#cb-guardar-mbway').prop("checked", true);
	$('#nome-iden-p-mbway').removeClass('dnonee');
	$('#caja-entera-rensr').removeClass('dnonee');
});

$('#nao_guardar_mbway').on('click', function(){
	$('#cb-guardar-mbway').prop("checked", false);
	$('#nome-iden-p-mbway').addClass('dnonee');
	$('#caja-entera-rensr').removeClass('dnonee');
});


$('#cancelar-mbway-s').on('click', function(){
	$('#cb-guardar-mbway').prop("checked", false);
	$('#nome-iden-p-mbway').addClass('dnonee');
	$('#caja-entera-rensr').addClass('dnonee');
	$('#input-rensr-mbw').val('');
	$('#input-rensr-mbw2').val('');
});

function apagar_pagamento(pagamento, url="", message = ""){

	$confirm = confirm(message);

	if($confirm==true){
		$.ajax({
	      url: url,
	      data:{
	          token: new Date().getTime(),
	          payment_id: pagamento,
	      },
	      method:'POST',
	      dataType: 'json',
		}).done(function(data){
	    	if(data.status=='SUCCESS'){
	    		alert(data.msg);
	    		location.reload();
	    	}else{
	    		alter(data.msg);
	    	}

	  	});
	}
	
}

