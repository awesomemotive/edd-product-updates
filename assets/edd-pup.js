jQuery(document).ready(function ($) {

	if( $('#prod-updates-email-preview-wrap').length ) {
		var emailPreview = $('#prod-updates-email-preview');
		$('#prod-updates-open-email-preview').colorbox({
			inline: true,
			href: emailPreview,
			width: '80%',
			height: 'auto'
		});
	}
	
		$('#cboxContent .closebutton').live('click', function(){
			$.fn.colorbox.close();
			
		});
		
		if ( $('.edd-pup-queue-button').length ){
			$('#edd-pup-view-queue-alert').colorbox({
					inline: true,
					href: $('#edd-pup-queue-details'),
					width: '95%',
					maxWidth: '680px',
					height: 'auto'			
			});
		}
		
		// used for queue resolution popup from alert
		//if ( $('.edd-pup-queue-button').length ){
			$('.edd-pup-queue-button').click( function() {
				
				var doClear = confirm('Empty the Queue?');
				
				if ( doClear ) {
					$.fn.colorbox.close();
				} else if ( ! doClear ) {
					alert('nevermind');
				}
	
			});
		//}
    
	function emailPreview() {
	
		var	button = $('#edd-pup-open-preview');
		
           button.mousedown( function() {
           
			   	tinyMCE.triggerSave();
			   		
  				}).click( function () {
  				
          		tinyMCE.triggerSave();
           		var url = document.URL,
           			form = $('#edd-pup-email-edit').serialize(),
           			data = {'action': 'edd_pup_ajax_preview', 'form' : form };         
                
                $.post( ajaxurl, data ).error( function() {
                
                        alert('Could not process emails. Please try again.');
						button.prop("disabled", false);
											
                    }).success( function( response ) {
                    
						$.colorbox({html:response});		
					
					});
            });
            
          }
            
	emailPreview();
	
	function emailTest() {
	
		var	button = $('#edd-pup-send-test');
		
           button.mousedown( function() {
           
			   	tinyMCE.triggerSave();
			   		
  				}).click( function () {
  				
          		tinyMCE.triggerSave();
           		var url = document.URL,
           			form = $('#edd-pup-email-edit').serialize(),
           			data = {'action': 'edd_pup_send_test_email', 'form' : form };
           			       
		   		if ( emailValidate( $('#from_email').val() ) ) {
		   		          
	                $.post( ajaxurl, data ).error( function() {
	                
	                        alert('Could not process emails. Please try again.');
							button.prop("disabled", false);
												
	                    }).success( function( response ) {
	                    	
							alert( response );
						
						});
				} else {
		             alert( 'Please enter a valid email address under "From Email."');
		             return false;				
				}
            });
            
          }
            
	emailTest();
	
	function emailValidate(email) {
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return regex.test(email);
	}
	
	function emailConfirmPreview() {
	
		var	button = $('#send-prod-updates'),
			spinner = $('.edd-pu-spin');
		
           button.mousedown( function () {
           		
           		tinyMCE.triggerSave();
           		
           		}).click( function() {

           		var url = document.URL,
		   		form = $('#edd-pup-email-edit').serialize(),
           		data = {'action': 'edd_pup_confirm_ajax', 'form' : form, 'url' : url };
		   		
		   		if ( emailValidate( $('#from_email').val() ) ) {
		   		
					$(this).prop("disabled",true);
					spinner.toggleClass('loading');         
	                $.post( ajaxurl, data ).error( function() {
	                
	                        alert('Could not process emails. Please try again.');
							spinner.toggleClass('loading');
							button.prop("disabled", false);
												
	                    }).success( function(r) {
	                    	
							if ( r === 'nocheck' ) {
							
								alert( 'Please choose at least one product whose customers will receive this email update.');
								spinner.toggleClass('loading');
								button.prop("disabled", false);	
														
							} else if ( r % 1 == 0 ) {
	
								var u = url.replace( 'add_pup_email', 'edit_pup_email');
								window.location.href= u + '&id=' + r + '&edd_pup_confirm=1';
								
							} else {
								
								$.colorbox({ html: r });
								spinner.toggleClass('loading');
								button.prop("disabled", false);
							}
	                    });
	              
	                    return false;    
	                    
				  } else {
	                
	                alert( 'Please enter a valid email address under "From Email."');
	                return false;

                  }
                
                });
            }
            
	emailConfirmPreview();
	
	function emailConfirmRedirect() {
		var	button = $('#send-prod-updates'),
			spinner = $('.edd-pu-spin'),
			url = document.URL,
			form = $('#edd-pup-email-edit').serialize(),
			data = {'action': 'edd_pup_confirm_ajax', 'form' : form, 'url' : url },
			confirm = decodeURIComponent((new RegExp('[?|&]' + 'edd_pup_confirm' + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
         
         if (confirm == 1) {
         
         	button.prop("disabled",true);
			spinner.toggleClass('loading');
			
		    $.post( ajaxurl, data ).error( function() {
	    
	            alert('Could not process emails. Please try again.');
				spinner.toggleClass('loading');
				button.prop("disabled", false);
									
	        }).success( function(r) {
					
				$.colorbox({ html: r });
				spinner.toggleClass('loading');
				button.prop("disabled", false);
				// Remove "edd_pup_confirm" url param once colorbox is loaded
				window.history.replaceState({}, 'newurl', url.replace(/&?edd_pup_confirm=([^&]$|[^&]*)/i, "") );
	        });
         }
	}
	emailConfirmRedirect();
	
	function eddPupAjaxEmails() {
		
		var button = $('#edd-pup-ajax'),
			clock = $('.progress-clock'),
			bar = $('.progress-bar'),
			emailid  = button.attr('data-email'),
			i = 0,
			s = 0,
			data = {
				'action': 'edd_pup_ajax_start',
				'email_id' : emailid
			};
		
		button.click( function() {
			
			$(this).prop('disabled', true);
			clock.timer('start');
			$.post(ajaxurl, data).error( function() {
			
				alert('something went wrong');
				
			}).success( function( totalEmails ) {
				
				button.prop('disabled', false).attr({
					'data-action': 'pause',
					value: 'Pause'});
				
				bar.attr('data-complete', '0');
				$('.progress-wrap').show();
				$('.progress-queue').text( totalEmails );
				
				eddPupAjaxTrigger(i, s, totalEmails);

			});
		});
			
		function eddPupAjaxTrigger(i, s, totalEmails) {
		
			if (+s >= +totalEmails) {
				eddPupAjaxEnd(i, s, totalEmails);
				return false;
			}
			
			$.post(ajaxurl, {'action':'edd_pup_ajax_trigger', 'iteration': i, 'sent' : s}).error( function() {
				alert('something went wrong');
				
				// Try to redo what went wrong, add e++. When e = 5, bail out of the operation completely. Set e back to 0 upon success.
							
			}).success( function(s) {
				
				function progressColor( color1, color2 ){
					bar.toggleClass(color1).toggleClass(color2);
				};
				
				var percent = Math.round((s / totalEmails) * 100);
				
					if (percent != e) {
						$('.progress-sent').text(s);
						bar.attr('data-complete', percent).css('width', percent+'%');
						$('.progress-percent').text(percent+'%');
						
						if ( percent <= 24 ) {
							progressColor('red', '');
						} else if ( percent >= 25 && percent <= 49 ) {
							progressColor('red','orange');
						} else if ( percent >= 50 && percent <= 74 ) {
							progressColor('orange','yellorange');
						} else if ( percent >= 75 && percent <= 99 ) {
							progressColor('yellorange', 'yellow');
						} else if ( percent == 100 ) {
							progressColor('yellow','green');
						}
						
						var e = percent;
					}
				
				i++;
				
				eddPupAjaxTrigger(i, s, totalEmails);
			});
		}
		
		function eddPupAjaxEnd(i,s,totalEmails){
			$.post(ajaxurl, {'action':'edd_pup_ajax_end'}).error( function() {
				alert('something went wrong');
			}).success( function (response) {
			
				button.prop('disabled', true).attr({
						'data-action': 'complete',
						value: 'Complete'});
						
					var t = clock.html().split(':');
				
				clock.timer('pause');
				$('.success-total').text(s);
				$('.success-time-s').text(t[1]);
				$('.success-time-m').text(t[0]);
				$('.success-time-h').text(t[0]);
				$('#completion').show();
			});
		}
		
		function eddPupAjaxPause(){
			
		}
		
		function eddPupAjaxResume(){
			
		}
		
		function eddPupAjaxRetry(seconds) {
	        if (this.paused) {
	            return;
	        }
	        this.set_status('request failed (retry in ' + seconds + 's)');
	        if (seconds) {
	            var me = this;
	            setTimeout(function() {
	                me.retry(--seconds);
	            }, 1000);
	        } else {
	            this.set_status('processing');
	            this.process();
	        }
	
		}
		
		/*function eddPupAjaxInitsss(){
	        var me = this;
	        jQuery('#cdn_export_file_start').click(function() {
	            if (this.value == 'Pause') {
	                me.paused = 1;
	                me.set_button_text('Resume');
	                me.set_status('paused');
	                clearInterval(me.timer);
	            } else {
	                if (this.value == 'Start') {
	                    me.offset = 0;
	                    me.seconds_elapsed = 0;
	                    me.clear_log();
	                    me.set_progress(0);
	                    me.set_elapsed('-');
	                }
	                me.paused = 0;
	                me.set_button_text('Pause');
	                me.set_status('processing');
	                me.timer = setInterval(function() {
	                    me.timer_callback();
	                }, 1000);
	            }
	
	            me.process();
	        });
	    
		}*/
	}
	eddPupAjaxEmails();

});// End of document ready

/*
 * =======================
 * jQuery Timer Plugin
 * =======================
 * 
 * Depends on:		jquery
 * 
 * --------
 * Summary:
 * --------
 * Start/Stop/Resume a time in any HTML element
 */

(function($){

	var Timer = function(element, options) {
		var defaults = {
			editable: true,			//this will let users make changes to the time
			restart: false,			//this will enable stop or continue after a timer callback
			repeat: false				//this will enable us to repeat the callback passed by user
		};

		this.options = $.extend(defaults, options);
		this.$el = $(element);
		this.element = element;	//to remove the Timer object on remove

		this.init();

	};

	/*
	Initialize the plugin with common properties
	*/
	Timer.prototype.init = function() {

		//setup
		this.secsNum           = 0;
		this.minsNum           = 0;
		this.hrsNum            = 0;
		this.secsStr           = "0 sec";
		this.minsStr           = "";
		this.hrsStr            = "";
		this.timerId           = null;
		this.delay             = 1000;
		this.isTimerRunning    = false;

		if (this.options.seconds !== undefined) {
			this.hrsNum = Math.floor(this.options.seconds / 3600);
			this.minsNum = Math.floor((this.options.seconds - (this.hrsNum * 3600))/60);
			this.secsNum = this.options.seconds - (this.hrsNum * 3600) - (this.minsNum * 60);

			this.timeToString();
		}
		
		this.elType = this.$el.prop('tagName').toLowerCase();

		if(this.options.editable) {
			this.initEditable();
		}

		/**
		 * Convert the duration to seconds (for notifications)
		 */
		if(this.options.duration) {
			
			this.duration = this.options.duration = this.convertToSeconds(this.options.duration); //duration increments by options.duration over time

		}

	};

	Timer.prototype.convertToSeconds = function(time) {
		//the duration can be a number or string
		//eg. 5m OR 5m30s or 2h15m30s OR 15
		
		//In case it s just a number, then use that as number of seconds
		if(!isNaN(Number(time))) {
			return time;
		}

		time = time.toLowerCase();

		//@todo: throw an error in case of faulty time value
		

		//Convert pretty time to seconds
		var seconds = 0;
		time.replace(/([0-9]{1,2}h)?([0-9]{1,2}m)?([0-9]{1,2}s)/, function($match, $1, $2, $3){
			if($1) seconds += Number($1.replace('h', '')) * 3600;
			if($2) seconds += Number($2.replace('m', '')) * 60;
			if($3) seconds += Number($3.replace('s', ''));
		});

		return seconds;
	};

	Timer.prototype.start = function () {
		if(!this.isTimerRunning) {
			this.updateTimerDisplay();
			this.incrementTime(); //to avoid the 1 second gap that gets created if the seconds are not incremented
			this.startTimerInterval();
		}
	};

	Timer.prototype.pause = function () {
		clearInterval(this.timerId);
		this.isTimerRunning = false;
	};

	Timer.prototype.resume = function () {
		if(!this.isTimerRunning) {
			this.startTimerInterval();
		}
	};

	Timer.prototype.remove = function () {
		this.pause();
		//clear timeout
		clearTimeout(this.timeOutId);
		//Use the original DOM element (not jQuery object) to remove data attributes
		$.removeData(this.element, 'plugin_' + pluginName);
		$.removeData(this.element, 'seconds');
	};


	Timer.prototype.startTimerInterval = function () {
		var self = this;
		this.timerId = setInterval(function() { self.incrementTime(); }, this.delay);
		this.isTimerRunning = true;	
	};

	/*
	Allow users to click and edit the timer value by typing in
	*/
	Timer.prototype.initEditable = function () {
		
		var self = this;

		this.$el.on('focus', function(){
			self.pause();
		});

		this.$el.on('blur', function(){

			//get the value and update the number of seconds if necessary
			var timerDisplayStr;
			var timerDisplayArr;

			//remove any spaces while getting the string
			if(self.elType === 'input' || self.elType === 'textarea') {
				timerDisplayStr = $(this).val().replace(/\s+/, '');
			} else {
				timerDisplayStr = $(this).html().replace(/\s+/, '');
			}

			//check for seconds
			//check for minutes
			//check for hours

			var matchSeconds  = /\d+sec/,
			matchMinutes  = /\d+\:\d+min/,
			matchHours    = /\d+\:\d+\:\d+/;

			if(timerDisplayStr.match(matchSeconds)) {

				//extract the seconds from this
				self.secsNum = parseInt(timerDisplayStr.replace(/sec/, ''), 10) + 1;
				if (self.secsNum > 59) {
					self.secsNum = 0;
					self.minsNum++;
				}

			} else if(timerDisplayStr.match(matchMinutes)) {

				timerDisplayStr = timerDisplayStr.replace(/min/, '');
				timerDisplayArr = timerDisplayStr.split(':');
				self.minsNum = parseInt(timerDisplayArr[0], 10);
				self.secsNum = parseInt(timerDisplayArr[1], 10) + 1;

				if (self.secsNum > 59) {
					self.secsNum = 0;
					self.minsNum++;
				}

				if (self.minsNum > 59) {
					self.minsNum = 0;
					self.hrsNum++;
				}

			} else if(timerDisplayStr.match(matchHours)) {

				timerDisplayArr = timerDisplayStr.split(':');
				self.hrsNum = parseInt(timerDisplayArr[0], 10);
				self.minsNum = parseInt(timerDisplayArr[1], 10);
				self.secsNum = parseInt(timerDisplayArr[2], 10) + 1;

				if (self.secsNum > 59) {
					self.secsNum = 0;
					self.minsNum++;
				}

				if (self.minsNum > 59) {
					self.minsNum = 0;
					self.hrsNum++;
				}

			}
			
			self.resume();
		});
	};



	Timer.prototype.updateTimerDisplay = function () {
		//if(this.hrsNum > 0) this.options.showHours = true;
		/*if(this.options.showHours) this.$el.html(this.hrsStr + ":" + this.minsStr + ":" + this.secsStr);
		else this.$el.html(this.minsStr + ":" + this.secsStr);*/
		var displayStr;

		if(this.hrsNum === 0) {
			if(this.secsNum < 60 && this.minsNum === 0) {
				displayStr = "00:" + this.secsStr;
			} else {
				displayStr = this.minsStr + ":" + this.secsStr;
			}
		} else {
			displayStr = this.hrsStr + ':' + this.minsStr + ':' + this.secsStr;
		}

		if(this.elType === 'input' || this.elType === 'textarea') {
			this.$el.val(displayStr);
		} else {
			this.$el.html(displayStr);
		}

		//assign the number of seconds to this element's data attribute for seconds
		this.$el.data('seconds', this.get_seconds());
	};

	Timer.prototype.timeToString = function () {
		this.secsStr = (this.secsNum < 10) ?  '0' + this.secsNum : this.secsNum;
		this.minsStr = (this.minsNum < 10) ?  '0' + this.minsNum : this.minsNum;
		this.hrsStr = this.hrsNum;
	};

	/*
	Get the timer's value in seconds
	*/
	Timer.prototype.get_seconds = function () {
		return ((this.hrsNum*3600) + (this.minsNum*60) + this.secsNum);
	};

	/**
	 * Notify - Call callback function if any when the options.duration is complete
	 */
	Timer.prototype.notify = function() {
		//If user has specified a callback, then use that or just alert a simple 'Time up!' message.
		if(this.options.callback) {
			this.options.callback();
		} else {
			alert('Time up!');
		}
		
	};

	Timer.prototype.incrementTime = function () {

		this.timeToString();
		this.updateTimerDisplay();

		/**
		 * Check if a duration was specified 
		 * If so pass control over to `notify` for a moment
		 */
		if(this.$el.data('seconds') === this.duration) {
			this.notify();
			if(this.options.repeat === true) {
				this.duration += this.options.duration;
			}
		}

		//increment
		this.secsNum++;
		if(this.secsNum % 60 === 0) {
			this.minsNum++;
			this.secsNum = 0;
		}

		//handle time exceeding 60 minsNum!
		if(this.minsNum > 59 && this.minsNum % 60 === 0)
		{
			this.hrsNum++;
			this.minsNum = 0;
		}

	};




	///////////////////////////////////////////////////
	///////////////INITIALIZE THE PLUGIN///////////////
	var pluginName = 'timer';
	$.fn[pluginName] = function(options) {

		options = options || 'start';


		return this.each(function() {

			/*
			Allow the plugin to be initialized on an element only once
			This way we can call the plugin's internal function
			without having to reinitialize the plugin all over again.
			*/
			if (!($.data(this, 'plugin_' + pluginName) instanceof Timer)) {

				/*
				Create a new data attribute on the element to hold the plugin name
				This way we can know which plugin(s) is/are initialized on the element later
				*/
				$.data(this, 'plugin_' + pluginName, new Timer(this, options));

			}

			/*
			Use the instance of this plugin derived from the data attribute for this element
			to conduct whatever action requested as a string parameter.
			*/
			var instance = $.data(this, 'plugin_' + pluginName);

			/*
			Provision for calling a function from this plugin
			without initializing it all over again
			*/
			if (typeof options === 'string') {
				if (typeof instance[options] === 'function') {
					/*
					Pass in 'instance' to provide for the value of 'this' in the called function
					*/
					instance[options].call(instance);
				}
			}

			/**
			 * Provision for passing an object for notification feature
			 */
			if( typeof options === 'object' ) {
				instance['start'].call(instance, options);
			}


		});
	};
	////////////////////////////////////////////////////
	////////////////////////////////////////////////////



})(jQuery);
