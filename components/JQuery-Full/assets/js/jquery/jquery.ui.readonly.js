/*
 * jquery-plugin Readonly
 *
 * Version 1.0-beta1
 * 
 * http://dev.powelltechs.com/jquery.readonly
 * http://plugins.jquery.com/project/readonly
 * 
 * Known good compatibility with jQuery 1.3.2
 * 
 * Please read the CHANGELOG for and/or bugzilla.
 * http://dev.powelltechs.com/bugzilla/dashboard.cgi?product=jquery.readonly
 *
 * For examples, please go to http://dev.powelltechs.com/jquery.readonly
 *
 * @todo Finish the documentation for this plugin.
 * @todo Do some half-decent comments in this javascript.
 * @todo Test this plugin with the major browsers, including
 *  IE6-8; FF3.0,3.5; Opera 9,10; Chrome/Chromium; Safari; Konqueror
 * @todo Figure out how to do automated javascript UI testing.
 *
 *
 * Copyright (c) 2009 Charlie Powell <powellc@powelltechs.com>
 * 
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 */
 
 
; // Yes, a random semicolon IS needed here, it's a jQuery thing.


// Define the default options for each readonly overlay object.
// In addition to other static methods.
window.com_powelltechs_readonly_obj = window.com_powelltechs_readonly_obj || {
	
	// Just the interval pointer.
	_interval: 0,
	
	defaults: {
		onClick: null,
		onDblClick: null,
		onFocus: null,
		onKeyPress: function(e){ if(e.keyCode == 9) return true; else return false; },
		overlayClass: 'readonly_overlay',
		title: '',
		zIndex: '100',
		
		_dummy: false
	},
	
	_elements: [],
	
	handleElement: function(el, opts){
		/*console.log('@todo do the attach logic.');
		console.log(el);
		console.log(opts);*/
		
		var obj;
		var actualEl;
		
		if(typeof(el.jquery) == 'undefined'){
			// el is an actual DOM node!
			actualEl = el;
		}
		else{
			actualEl = el[0];
		}
		
		// Try to reuse an existing object if it is bound to the dom node.
		if(typeof(actualEl.com_powelltechs_readonly) != 'undefined'){
			obj = actualEl.com_powelltechs_readonly;
		}
		else{
			obj = new com_powelltechs_readonly();
			obj.attachToElement(el);
		}
		
		obj.setOptions(opts);
	},
	
	init: function(){
		if(window.com_powelltechs_readonly_obj._interval != 0) return;
		window.com_powelltechs_readonly_obj._interval = setInterval(window.com_powelltechs_readonly_obj._tick, 500);
	},
	
	_tick: function(){
		//console.log('tock'); return;
		for(i in window.com_powelltechs_readonly_obj._elements){
			window.com_powelltechs_readonly_obj._elements[i].updateOverlay();
		}
	},
	
	_registerObject: function(obj){
		window.com_powelltechs_readonly_obj._elements.push(obj);
	},
	
	_unregisterObject: function(obj){
		for(i in window.com_powelltechs_readonly_obj._elements){
			if(window.com_powelltechs_readonly_obj._elements[i] == obj){
				window.com_powelltechs_readonly_obj._elements.splice(i, 1);
				return;
			}
		}
	},	
	

	// KEEP THIS THE LAST ELEMENT
	//  It's a trick to prevent the final-comma error in IE.
	_dummy: false
};


// Each individual readonly instance will be its own version of this object.
// This allows for a greater amount of flexibility for defining options.
function com_powelltechs_readonly(){
	// First, define all the properties of this new object.
	this.elementBound = null;
	
	// The overlay object.
	this.elementOverlay = null;
	
	// Any options currently set for this object.
	this.options = jQuery.extend({}, window.com_powelltechs_readonly_obj.defaults);
	
	this.isIEHack = false;
	
	this.cache = {};
	
	this.isActive = function(){
		return (this.elementOverlay != null);
	};
	
	this.setOptions = function(opts){

		var toggled = false;
		var gogo = null;
		var forceActive = false;
		
		// It's not a good idea to change options on a currently active element.
		if(this.isActive()){
			this.unsetOverlay();
			toggled = true;
			forceActive = true;
		}
		
		
		if(typeof(opts) == 'object'){
			for (i in opts){
				if(i == 'active' || i == 'enabled')
					gogo = opts[i];
				else if(i == 'toggle')
					gogo = !this.isActive();
				else
					this.options[i] = opts[i];
			}
		}
		
		if(gogo === true || opts === true){
			this.setOverlay();
			forceActive = false;
		}
		else if(gogo === false || opts === false){
			this.unsetOverlay();
			forceActive = false;
		}
		else if(opts == 'toggle'){
			// Do not toggle if it was toggled automatically!
			if(!toggled) this.toggle();
			forceActive = false;
		}
		
		// If it was unset at the beginning of the function... reset it as active.
		if(forceActive)
			this.setOverlay();
	};
	
	// Main function to set an overlay on an element.
	// Will handle all the internals such as internal indexing, positioning,
	// etc...
	this.setOverlay = function(){
		
		if(this.isActive()){
			return;
		};
		
		this.elementOverlay = jQuery('<div class="' + this.options.overlayClass + '" title="' + this.options.title + '"></div>');
		this.elementOverlay.appendTo('body');
		this.elementOverlay.css('position', 'absolute').css('z-index', this.options.zIndex);
		
		// Update any events on this overlay, such as click, dblclick, and focus.
		if(this.options.onClick != null)
			this.elementOverlay.bind('click', this.options.onClick);
		
		if(this.options.onDblClick != null)
			this.elementOverlay.bind('dblclick', this.options.onDblClick);
		
		if(this.options.onFocus != null)
			this.elementBound.bind('focus', this.options.onFocus);
		// Force the original object to reject focus events!
		else
			this.elementBound.attr('tabindex', '-1');
		
		if(this.options.onKeyPress != null)
			this.elementBound.bind('keypress', this.options.onKeyPress);
		
		
		//el.bind('focus', this.bindUnfocus).after(overlay);
		//this._updateOverlay(el);
		
		if(this.isIEHack)
			this.elementBound.css('visibility', 'hidden');
		
		// Update the overlay positioning.
		this.updateOverlay();
		
		// Finally, register with the global list of overlays so they can be kept track of.
		window.com_powelltechs_readonly_obj._registerObject(this);
	};

	// Main function to unset an overlay from an element.
	// Will handle all the internals such as internal indexing, positioning,
	// etc...
	this.unsetOverlay = function(){
		if(!this.isActive()){
			return;
		};
		
		this.elementOverlay.remove();
		this.elementOverlay = null;
		
		//el.unbind('focus', this.bindUnfocus);
		
		if(this.isIEHack)
			this.elementBound.css('visibility', 'visible');
		
		// Clear the cache dimensions.
		this.cache.dimensions = {
				width: 0,
				height: 0,
				left: 0,
				top: 0
		};
		
		
		if(this.options.onFocus != null)
			this.elementBound.unbind('focus', this.options.onFocus);
		// I guess... if the original objects wants its focus back...
		else
			this.elementBound.attr('tabindex', '0');
		
		if(this.options.onKeyPress != null)
			this.elementBound.unbind('keypress', this.options.onKeyPress);
		
		// Finally, unregister with the global list of overlays.
		window.com_powelltechs_readonly_obj._unregisterObject(this);
	};
	
	// Update a jQuery element's overlay position, useful for window resizing and
	//  initial setting on the element.
	this.updateOverlay = function(){
		if(!this.isActive()) return;
		
		var d = this.getDimensions();
		var c = this.cache.dimensions;
		var doAll = (typeof(c) == 'undefined')? true : false;
		
		// Do these new dimensions match the cached ones?
		if(doAll || d.width != c.width) this.elementOverlay.css('width', d.width);
		if(doAll || d.height != c.height) this.elementOverlay.css('height', d.height);
		if(doAll || d.top != c.top) this.elementOverlay.css('top', d.top);
		if(doAll || d.left != c.left) this.elementOverlay.css('left', d.left);

		// Cache this information for any future checks.
		this.cache.dimensions = d;
	};
	
	this.toggle = function(){
		if(this.isActive()) this.unsetOverlay();
		else this.setOverlay();
	};
	
	this.attachToElement = function(el){
		if(typeof(el) == 'undefined')
			this.unbind();
		else
			this.bind(el);
	};
	
	this.bind = function(el){
		
		this.elementBound = jQuery(el);
		this.elementBound[0].com_powelltechs_readonly = this;
		
		
		// IE version 6 was so wonderful.... wasn't it?.....
		// @see http://blogs.msdn.com/ie/archive/2006/01/17/514076.aspx
		if(this.elementBound[0].tagName == 'SELECT' && jQuery.browser.version == '6.0' && jQuery.browser.msie)
			this.isIEHack = true;
	};
	
	this.unbind = function(){
		if(this.elementBound == null) return;
		if(typeof(this.elementBound.com_powelltechs_readonly) != 'undefined') this.elementBound.com_powelltechs_readonly = null;
		this.elementBound = null;
	};
	
	
	
	
	// Get dimensions for a jQuery element.
	//  Internally function, but could probably be used by anything.
	// @return object { width, height, top, left }
	this.getDimensions = function(){
		var ret = {
			width: 0,
			height: 0,
			top: 0,
			left: 0
		};
		
		if(this.elementBound == null){
			return ret;
		}
		
		// The multiple acquisitions of the CSS styles are required to cover any border and padding the elements may have.
		// The Ternary (parseInt(...) || 0) statements fix a bug in IE6 where it returns NaN,
		//  which doesn't play nicely when adding to numbers...
		
		ret.width = this.elementBound.width() 
		  + (parseInt(this.elementBound.css('borderLeftWidth')) || 0)
		  + (parseInt(this.elementBound.css('borderRightWidth')) || 0)
		  + (parseInt(this.elementBound.css('padding-left')) || 0)
		  + (parseInt(this.elementBound.css('padding-right')) || 0);
		ret.height = this.elementBound.height() 
		  + (parseInt(this.elementBound.css('borderTopWidth')) || 0) 
		  + (parseInt(this.elementBound.css('borderBottomWidth')) || 0)
		  + (parseInt(this.elementBound.css('padding-bottom')) || 0)
		  + (parseInt(this.elementBound.css('padding-bottom')) || 0);
		var offsets = this.elementBound.offset();
		
		var zoom = 1;
		/*
		if(document.body.clientWidth){
			var b = document.body.getBoundingClientRect();    
			zoom = (b.right - b.left) / document.body.clientWidth;
		}
		*/
		ret.left = offsets.left * zoom;
		ret.top = offsets.top * zoom;
		
		return ret;
	};
	
	
	// Lastly, do any logic required in the constructor.
	//this.setOptions(options);
		
};


(function(jQuery) {

  jQuery.extend(jQuery.fn, {
	// jQuery wrapper around the global handler object.
	readonly : function(options) {
	  
	  // Init the global object if not already, can be called multiple times.
	  window.com_powelltechs_readonly_obj.init();
	  
	  // If no status was given, set it to true.
	  if (options == undefined) options = true;
	  
	  // Run through each element given in by the programmer.
	  jQuery(this).each(function(){
		  window.com_powelltechs_readonly_obj.handleElement(this, options);
	  });
	  return this;
	}
  });
})(jQuery);

/* END OF FILE jquery.readonly */
