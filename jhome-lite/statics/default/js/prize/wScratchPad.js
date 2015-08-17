/******************************************
 * Websanova.com
 *
 * Resources for web entrepreneurs
 *
 * @author          Websanova
 * @copyright       Copyright (c) 2012 Websanova.
 * @license         This wScratchPad jQuery plug-in is dual licensed under the MIT and GPL licenses.
 * @link            http://www.websanova.com
 * @github			http://github.com/websanova/wScratchPad
 * @version         Version 1.4.2
 *
 ******************************************/
(function($)
{
	$.fn.wScratchPad = function(option, settings)
	{
		if(typeof option === 'object')
		{
			settings = option;
		}
		else if(typeof option == 'string')
		{
			var values = [];

			var elements = this.each(function()
			{
				var data = $(this).data('_wScratchPad');

				if(data)
				{
					if(option === 'reset') { data.reset(); }
					else if(option === 'clear') { data.clear(); }
					else if(option === 'enabled') { data.enabled = settings === true; }
					else if($.fn.wScratchPad.defaultSettings[option] !== undefined)
					{
						if(settings !== undefined) { data.settings[option] = settings; }
						else { values.push(data.settings[option]); }
					}
				}
			});

			if(values.length === 1) { return values[0]; }
			if(values.length > 0) { return values; }
			else { return elements; }
		}
		
		settings = $.extend({}, $.fn.wScratchPad.defaultSettings, settings || {});

		return this.each(function()
		{
			var elem = $(this);
			var $settings = jQuery.extend(true, {}, settings);

			//test for HTML5 canvas
			var test = document.createElement('canvas');
			if(!test.getContext)
			{
				elem.html("Browser does not support HTML5 canvas, please upgrade to a more modern browser.");
				return false;	
			}

			var sp = new ScratchPad($settings, elem);
			
			elem.append(sp.generate());
			
			//get number of pixels of canvas for percent calculations 
			sp.pixels = sp.canvas.width * sp.canvas.height;
			
			elem.data('_wScratchPad', sp);
			
			sp.init();
		});
	};

	$.fn.wScratchPad.defaultSettings =
	{
		width		: 210,						// set width - best to match image width
		height		: 100,						// set height - best to match image height
		image		: 'images/slide1.jpg',		// set image path
		image2		: null,						// set overlay image path - if set color is not used
		color		: '#336699',				// set scratch color - if image2 is not set uses color
		overlay		: 'none',					// set the type of overlay effect 'none', 'lighter' - only used with color
		size		: 10,						// set size of scratcher
		scratchDown	: null,						// scratchDown callback
		scratchUp	: null,						// scratchUp callback
		scratchMove	: null,						// scratcMove callback
		cursor		: null						// Set path to custom cursor
	};
	
	function ScratchPad(settings, elem)
	{
		this.sp = null;
		this.settings = settings;
		this.$elem = elem;
		
		this.enabled = true;
		this.scratch = false;
		
		this.canvas = null;
		this.ctx = null;
		
		return this;
	}
	
	ScratchPad.prototype = 
	{
		generate: function()
		{
			var that = this;
			
			this.canvas = document.createElement('canvas');
			this.ctx = this.canvas.getContext('2d');
			
			this.sp =
			$('<div></div>')
			.css({position: 'relative'})
			.append(
				$(this.canvas)
				.attr('width', this.settings.width + 'px')
				.attr('height', this.settings.height + 'px')
			)
			
			$(this.canvas)
			.mousedown(function(e)
			{
				if(!that.enabled) return true;

				e.preventDefault();
				e.stopPropagation();
				
				//reset canvas offset in case it has moved
				that.canvas_offset = $(that.canvas).offset();
				
				that.scratch = true;
				that.scratchFunc(e, that, 'Down');
			})
			.mousemove(function(e)
			{
				e.preventDefault();
				e.stopPropagation();
				
				if(that.scratch) that.scratchFunc(e, that, 'Move');
			})
			.mouseup(function(e)
			{
				e.preventDefault();
				e.stopPropagation();
				
				//make sure we are in draw mode otherwise this will fire on any mouse up.
				if(that.scratch)
				{
					that.scratch = false;
					that.scratchFunc(e, that, 'Up');
				}
			});

			this.bindMobile(this.sp);
			
			return this.sp;
		},
		
		bindMobile: function($el)
		{
			$el.bind('touchstart touchmove touchend touchcancel', function ()
			{
				var touches = event.changedTouches, first = touches[0], type = ""; 

				switch (event.type)
				{
					case "touchstart": type = "mousedown"; break; 
					case "touchmove": type = "mousemove"; break; 
					case "touchend": type = "mouseup"; break; 
					default: return;
				}

				var simulatedEvent = document.createEvent("MouseEvent"); 

				simulatedEvent.initMouseEvent(type, true, true, window, 1, first.screenX, first.screenY, first.clientX, first.clientY, false, false, false, false, 0/*left*/, null);
				first.target.dispatchEvent(simulatedEvent);
				event.preventDefault();
			});
		},

		init: function()
		{
			this.sp.css('width', this.settings.width);
			this.sp.css('height', this.settings.height);
			this.sp.css('cursor', (this.settings.cursor ? 'url("' + this.settings.cursor + '"), default' : 'default'));

			$(this.canvas).css({cursor: (this.settings.cursor ? 'url("' + this.settings.cursor + '"), default' : 'default')});
			
			this.canvas.width = this.settings.width;
			this.canvas.height = this.settings.height;
			
			this.pixels = this.canvas.width * this.canvas.height;
			
			if(this.settings.image2)
			{
				this.drawImage(this.settings.image2);
			}
			else
			{
				if(this.settings.overlay != 'none')
				{
					if(this.settings.image)
					{
						this.drawImage(this.settings.image);
					}
					this.ctx.globalCompositeOperation = this.settings.overlay;
				}
				else
				{
					this.setBgImage();
				}
				
				this.ctx.fillStyle = this.settings.color;
				this.ctx.beginPath();
				this.ctx.rect(0, 0, this.settings.width, this.settings.height)
				this.ctx.fill();
			}
		},

		reset: function()
		{
			this.ctx.globalCompositeOperation = 'source-over';
			this.init();
		},

		clear: function()
		{
			this.ctx.clearRect(0, 0, this.settings.width, this.settings.height);
		},

		setBgImage: function()
		{
			if(this.settings.image)
			{
				this.sp.css({backgroundImage: 'url('+this.settings.image+')'});
			}
		},

		drawImage: function(imagePath)
		{
			var that = this;
			var img = new Image();
  			img.src = imagePath;
  			$(img).load(function(){
  				that.ctx.drawImage(img, 0, 0);
  				that.setBgImage();
  			})
		},

		scratchFunc: function(e, that, event)
		{
			e.pageX = Math.floor(e.pageX - that.canvas_offset.left);
			e.pageY = Math.floor(e.pageY - that.canvas_offset.top);
			
			that['scratch' + event](e, that);
			
			if(that.settings['scratch' + event]) that.settings['scratch' + event].apply(that, [e, that.scratchPercentage(that)]);
		},

		scratchPercentage: function(that)
		{
			var hits = 0;
			var imageData = that.ctx.getImageData(0,0,that.canvas.width,that.canvas.height)
			
			for(var i=0, ii=imageData.data.length; i<ii; i=i+4)
			{
				if(imageData.data[i] == 0 && imageData.data[i+1] == 0 && imageData.data[i+2] == 0 && imageData.data[i+3] == 0) hits++;
			}
			
			return (hits / that.pixels) * 100;
		},

		scratchDown: function(e, that)
		{
			that.ctx.globalCompositeOperation = 'destination-out';
			that.ctx.lineJoin = "round";
			that.ctx.lineCap = "round";
			that.ctx.strokeStyle = that.settings.color;
			that.ctx.lineWidth = that.settings.size;
			
			//draw single dot in case of a click without a move
			that.ctx.beginPath();
			that.ctx.arc(e.pageX, e.pageY, that.settings.size/2, 0, Math.PI*2, true);
			that.ctx.closePath();
			that.ctx.fill();
			
			//start the path for a drag
			that.ctx.beginPath();
			that.ctx.moveTo(e.pageX, e.pageY);
		},
		
		scratchMove: function(e, that)
		{
			that.ctx.lineTo(e.pageX, e.pageY);
			that.ctx.stroke();
		},
		
		scratchUp: function(e, that)
		{
			that.ctx.closePath();
		},
	}
})(jQuery);