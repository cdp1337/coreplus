/**
 * Utility for managing users in coreplus via javascript
 * (particularly the current user)
 */


// This function() creates a private scope for variables that do not need to be globally visible.
(function(){
	
	var ready = false,
		initstack = [],
		serverdat = null;


	// Only initialize the user object once, (just in case this file is included multiple times).
	Core.User = Core.User || {
		/**
		 * Initialization function to load any of the serverside scripts as necessary.
		 *
		 * If a function is sent as the parameter, that function is automatically called as soon as the
		 * User system is ready.
		 *
		 * @param f function
		 */
		init: function(f){
			// Only do initialization once.
			if(ready){

				// Call the user's function if requested.
				if(f) f();

				return true;
			}

			// Push the user's function if requested.
			if(f){
				initstack.push(f);
			}
			
			// Load the data from the server
			$.ajax({
				url: Core.ROOT_URL + 'UserController/jshelper.json',
				dataType: 'json',
				success: function(dat){
					serverdat = dat;
					ready = true;
					
					if(initstack.length){
						for(i in initstack){
							initstack[i]();
						}
					}
				}
			})
		},

		/**
		 * Listener for initialization.  This can be called with functions that must be ran after
		 * init has been ran.
		 * If init has already been executed, the function is called immediately.
		 *
		 * @param f function
		 */
		oninit: function(f){
			if(ready) f();
			else initstack.push(f);
		},

		AccessStrings: {
			/**
			 * Render the access string as valid HTML that can be appended to the doc as necessary.
			 * Currently, this instance of the code should only be in the DOM at any one time, since all the classes
			 * for this element will be the same, (it's not instanced when render is requested)
			 *
			 * @param opts Object
			 * @return String
			 */
			render: function(opts){

				if(!ready){
					$.error('Please make sure that Core.User.init() is called BEFORE the action strings object is requested.');
					return false;
				}

				opts = $.extend({
					name: 'accessstring',
					class: '',
					title: 'Access Permissions',
					value: '*',
					description: ''
				}, opts);

				// Will be available from the server, thanks server!
				var tpl = serverdat.accessstringtemplate,
					type = 'whitelist',
					checked = null,
					groupschecked = [],
					e, i, ev, t;

				// Replace the variables with the requested data
				tpl = tpl
					.replace('##CLASS##', opts.class)
					.replace('##NAME##', opts.name)
					.replace('##TITLE##', opts.title)
					.replace('##DESCRIPTION##', opts.description);

				opts.value = opts.value.trim();

				// Handle the actual access string itself now.
				if(opts.value == '*'){
					checked = 'basic_anyone';
				}
				else if(!opts.value){
					// Blank value
					checked = 'advanced';
				}
				else if(opts.value == 'g:anonymous'){
					checked = 'basic_anonymous';
				}
				else if(opts.value == 'g:authenticated'){
					checked = 'basic_authenticated';
				}
				else{
					// Determine the sub groups checked.
					checked = 'advanced';
					e = opts.value.split(';');
					for(i in e){
						ev = e[i].trim();

						// If a wildcard is present, mark the groups as ones to blacklist.
						if(ev == '*'){
							type = 'blacklist';
							continue;
						}

						t = ev.split(':');
						// Trim off the '!' in front of it, it'll be picked up by the presence of the '*' at the end.
						if(t[1].substr(0, 1) == '!') t[1] = t[1].substr(1);
						groupschecked.push(t[1]);
					}
				}

				// Now that I have checked and groupschecked, (if necessary)...
				tpl = tpl.replace('value="' + checked + '"', 'value="' + checked + '" checked="checked"');
				for(i in groupschecked){
					tpl = tpl.replace('value="' + groupschecked[i] + '"', 'value="' + groupschecked[i] + '" checked="checked"');
				}


				if(checked == 'advanced'){
					// And white/black list
					tpl = tpl.replace('value="' + type + '"', 'value="' + type + '" checked="checked"');

					// I have to un-hide that section :/
					tpl = tpl.replace(/_advanced" style="display:none;"/g, '_advanced"');
				}

				return tpl;
			},

			/**
			 * Parse a DOM structure for the key inputs that are used in access string elements back into a single access string.
			 *
			 * @requires JQuery
			 * @param d DOMElement
			 * @return String
			 */
			parsedom: function(d){

				// Ensure this object is jquery-ified.  This makes parsing it MUCH more simple!
				var $d = $(d),
					type = 'whitelist',
					prefix = '',
					g = [];

				if($d.find('input[value=basic_anyone]').is(':checked')){
					return '*';
				}
				else if($d.find('input[value=basic_anonymous]').is(':checked')){
					return 'g:anonymous';
				}
				else if($d.find('input[value=basic_authenticated]').is(':checked')){
					return 'g:authenticated';
				}
				else if($d.find('input[value=advanced]').is(':checked')){
					// Toggles the white/black list trigger.
					if($d.find('input[value=blacklist]').is(':checked')){
						type = 'blacklist';
						prefix = '!';
					}

					// And adds the groups.
					$d.find('input[type=checkbox]:checked').each(function(){
						g.push('g:' + prefix + $(this).val());
					});

					// If it's a blacklist, everyone else is allowed!
					if(type == 'blacklist') g.push('*');

					return g.join(';');
				}
				else{
					// umm...
					return '';
				}
			}
		}
	};
})();
