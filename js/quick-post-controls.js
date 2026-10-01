/*
 * quick-posts-controls.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/quick-posts-controls.js
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 * Copyright (c) 2013 undido <firekid109@hotmail.com>
 * Copyright (c) 2013-2014 Marcin Łabanowski <marcin@6irc.net>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/quick-post-controls.js';
 *
 */

$(document).ready(function(){
	var open_form = function() {
		var thread = $(this).parent().parent().hasClass('op');
		var id = $(this).attr('name').match(/^delete_(\d+)$/)[1];
		var submitButton;
		
		if(this.checked) {
			var post_form = $('<form>', {
				'class': 'post-actions',
				'method': 'post',
				'style': 'margin:10px 0 0 0'
			});
			var post_form_container = $('<div>', { style: 'text-align:right' });

			if(!thread) {
				post_form_container.append($('<hr>'));
			}

			post_form_container
				.append($('<input>', { type: 'hidden', name: 'delete_' + id }))
				.append($('<label>', { 'for': 'password_' + id }).text(_("Password")))
				.append(': ')
				.append($('<input>', { id: 'password_' + id, type: 'password', name: 'password', size: 11, maxlength: 18 }))
				.append($('<input>', { title: _('Delete file only'), type: 'checkbox', name: 'file', id: 'delete_file_' + id }))
				.append($('<label>', { 'for': 'delete_file_' + id }).text(_('File')))
				.append(' ')
				.append($('<input>', { type: 'submit', name: 'delete', value: _('Delete') }))
				.append($('<br>'))
				.append($('<label>', { 'for': 'reason_' + id }).text(_('Reason')))
				.append(': ')
				.append($('<input>', { id: 'reason_' + id, type: 'text', name: 'reason', size: 20, maxlength: 100 }))
				.append(' ')
				.append($('<input>', { type: 'submit', name: 'report', value: _('Report') }));

			post_form.append(post_form_container);
			post_form
				.attr('action', $('form[name="post"]:first').attr('action'))
				.append($('input[name=board]:first').clone())
				.find('input:not([type="checkbox"]):not([type="submit"]):not([type="hidden"])').keypress(function(e) {
					if(e.which == 13) {
						e.preventDefault();
						if($(this).attr('name') == 'password')  {
							post_form.find('input[name=delete]').click();
						} else if($(this).attr('name') == 'reason')  {
							post_form.find('input[name=report]').click();
						}
						
						return false;
					}
					
					return true;
				});
			
			post_form.find('input[type="password"]').val(localStorage.password);
			
			if(thread) {
				post_form.prependTo($(this).parent().parent().find('div.body'));
			} else {
				post_form.appendTo($(this).parent().parent());
				//post_form.insertBefore($(this));
			}
			
			$(window).trigger('quick-post-controls', post_form);
		} else {
			var elm = $(this).parent().parent().find('form');
			
			if(elm.attr('class') == 'post-actions')
				elm.remove();
		}
	};
	
	var init_qpc = function() {
		$(this).change(open_form);
		if(this.checked)
			$(this).trigger('change');
	};

	$('div.post input[type=checkbox].delete').each(init_qpc);

	$(document).on('new_post', function(e, post) {
		$(post).find('input[type=checkbox].delete').each(init_qpc);
	});
});

