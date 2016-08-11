(function(Core, $){
	"use strict";
	
	Core.ProgressLogIframe = function(logname, formid){
		var go = null,
			$wrapper = $('#' + logname + '-container'),
			$detailsLink = $wrapper.find('.progress-log-view-details'),
			$iframe = $wrapper.find('.progress-log-iframe');

		$detailsLink.click(function() {
			var $this = $(this);
			//console.log($iframe.is(':visible'));

			if($iframe.is(':visible')){
				$iframe.hide();
				$this.html('View Details');
			}
			else{
				$iframe.show();
				$this.html('Hide Details');
			}

			return false;
		});
		
		$('#' + formid).submit(function() {
			var prevMessage = '',
				$title = $wrapper.find('.progress-log-title'), 
				$progressInner = $wrapper.find('.progress-log-progressbar-inner'),
				$message = $wrapper.find('.progress-log-message'),
				$warnings = $wrapper.find('.progress-log-warnings'),
				warningsPrinted = [],
				iframe = $iframe[0],
				processText;
			
			processText = function(){
				var iframeDocument = iframe.contentDocument || iframe.contentWindow.document,
					newMessage = (iframeDocument.body ? iframeDocument.body.innerHTML : ''),
					changedContent = '',
					data, i, k, t, m, c;

				if(newMessage === prevMessage){
					// Nothing to do, message has not changed.
					return;
				}

				// What is the new content for this request?
				changedContent = newMessage.substr(prevMessage.length);

				prevMessage = newMessage;

				if(changedContent.indexOf('<!--CLI-DATA:') !== -1){
					data = changedContent.match(/<!--CLI-DATA:(.*?)-->/gm);

					for(i in data){
						//console.log(data[i]);

						k = data[i].substring(13, data[i].length-3);
						if(k.indexOf('PROGRESSBAR=') === 0){
							$progressInner.css('width', k.substr(12) + '%');
						}
						else if(k.indexOf('HEADER=') === 0){
							m = k.substr(7);
							$title.html(m);
						}
						else if(k.indexOf('LINE=') === 0 && k.indexOf(';TYPE=') !== -1){
							m = k.substring(5, k.indexOf(';TYPE='));
							t = k.substr(k.indexOf(';TYPE=') + 6);

							if(t === 'error'){
								c = 'icon-exclamation-triangle';
							}
							else if(t === 'warning'){
								c = 'icon-exclamation-circle';
							}
							else {
								c = '';
							}

							if(c){
								// This gets appended to the warnings too if it's new!
								if(warningsPrinted.indexOf(m) === -1){
									$warnings.append('<i class="icon ' + c + '"></i> ' + m + '<br/>');
									warningsPrinted.push(m);
								}
								m = '<i class="icon ' + c + '"></i> ' + m;
							}

							$message.html(m);
							if(t === 'error' && !$iframe.is(':visible')){
								// If there was an error, display the console!
								$detailsLink.click();
							}
						}
						else if(k.indexOf('LINE=') === 0){
							$message.html(k.substr(5));
						}
						else{
							console.log('Unknown parameter: ' + k);
						}
					}
					//console.log(data);
				}
			};
			
			$iframe.load(function(){
				clearInterval(go);
				processText();
				$progressInner.css('width', '100%');
				$title.html('DONE');
			});

			$progressInner.css('width', '0%');
			$title.html('Loading...');
			$wrapper.show();
			
			go = setInterval(processText, 250);
		});
	};
})(window.Core, window.jQuery);