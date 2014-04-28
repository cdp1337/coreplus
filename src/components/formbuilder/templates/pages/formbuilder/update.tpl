{css src="assets/css/formbuilder.css"}{/css}

<form id="form-preview">

</form>

{$form->render('head')}


{$form->render('body')}

{$form->render('foot')}

{script library='jquery'}{/script}

{script}<script>

	$(function(){
		var $addfield = "<button id='add-form-field'><i class='icon-add'></i>Add Field</button>";
		$('#formtabsgroup-form-elements').html("<div class='custom-field'>" + $('#formtabsgroup-form-elements').html() + "</div>");

		var $origfields = $('.custom-field').clone();

		$('.custom-field').addClass('expanded');


		// create the add field button
		$('#formtabsgroup-form-elements').append($addfield);

		$('#add-form-field').click(function(e){
			e.preventDefault();

			$('#formtabsgroup-form-elements').prepend($origfields.clone());

			$('select').minimalect();
		});


		$('form').on('click', 'input,textarea', function(e){
			if( $(this).closest('.custom-field').hasClass('expanded') ){
				e.stopPropagation();
			}

		});

		$('form').on('click', '.custom-field',
				function(){
					if(!$(this).hasClass('expanded')) {
						$('.custom-field').removeClass('expanded');
						$(this).addClass('expanded');
					} else {
						$(this).removeClass('expanded');
					}

				}
		);

		/*$('form').on('mouseout', '.custom-field',
				function(){
					if($(this).hasClass('expanded')) {
						$(this).removeClass('expanded');
					}
				}
		);*/



		$('form').on('keyup', '.custom-field input, .custom-field textarea', function(){
			formRenderPreview();
		});

		var preview = [];

		var formRenderPreview = function(){
			var $fields = $('#formtabsgroup-form-elements');
			preview = [];

			$fields.find('.custom-field').each(function(){
				var $field = $(this),
					eltype = $field.find('.formelement.fieldtype select').val(),
					ellabel = $field.find('.formelement.formtextinput-fieldmodel-name input').val(),
				    elname  = ellabel.replace(/[, ]+/g,'-').trim(),
					placeholder = $field.find('.formelement.formtextinput-fieldmodel-placeholder input').val();


				if(eltype == 'text'){
					preview.push("<div class='formelement formtextelement'><label class='form-element-label' for='" + elname + "'>" + ellabel + "</label><div class='form-element-value'><input type='text' id='' name='" + elname + "' placeholder='" + placeholder +"'></input></div></div>");
				}
				else if(eltype == 'textarea'){
					preview.push("<div class='formelement formtextareaelement'><label for='" + elname + "'>" + ellabel + "</label><div class='form-element-value'><textarea id='' name='" + elname + "' placeholder='" + placeholder +"'></textarea></div></div>");
				}
				else if(eltype == 'email'){

				}
				else if(eltype == 'select'){

				}
				else if(eltype == 'multiselect'){

				}
				else if(eltype == 'date'){

				}
				else if(eltype == 'time'){

				}
				else if(eltype == 'datetime'){

				}
				else if(eltype == 'radio'){

				}
				else if(eltype == 'state'){

				}
				else if(eltype == 'country'){

				}
				else {

				}

			});

			$('#form-preview').html(preview);
		}

	});

</script>{/script}