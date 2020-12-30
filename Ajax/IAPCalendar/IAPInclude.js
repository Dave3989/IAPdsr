	$(document).ready(function() {	

		var mykey = 'AIzaSyDxIZCg_Foy5p8Hf5YvCjSDlFzubvl0niw';
		var calendarid = 'en.usa#holiday@group.v.calendar.google.com';

		$.ajax({
			type: 'GET',
			url: encodeURI('https://www.googleapis.com/calendar/v3/calendars/' + calendarid+ '/events?key=' + mykey),
			dataType: 'json',
			success: function (calResponse) {
				var calHolidays = calResponse;
					//do whatever you want with each
			},
			error: function (calResponse) {
				var calError = calResponse;				
					//tell that an error has occurred
			}
		});

		var date = new Date();
		var d = date.getDate();
		var m = date.getMonth();
		var y = date.getFullYear();
		var prevYear = y - 1;
		var nextYear = y + 1;

		var urlin = document.URL;
		var uarray = urlin.split("/");
		var ln = "";
		for (i = 0; i <= uarray.length; i++) {
			ln = uarray.pop();
			var lnsm = ln.toLowerCase();
			if (lnsm.indexOf("litehausconsulting") >= 0) {
				uarray.push(ln);
				var lhcpath = uarray.join("/");
				break;
			}
			var lhcapp = ln;
		}


// http://www.google.com/calendar/feeds/usa__en%40holiday.calendar.google.com/public/basic',

		var calendar = $('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			defaultView: 'month',
			editable: true,
			eventSources:
			[
				{
					url: lhcpath+'/IAP/Ajax/IAPCalendar/IAPGetEvents.php?LCHA='+document.getElementById("LCHA").value
				},
				{
					url: 'https://www.googleapis.com/calendar/v3/calendars/en.usa#holiday@group.v.calendar.google.com/events?key=AIzaSyDxIZCg_Foy5p8Hf5YvCjSDlFzubvl0niw',
					dataType: 'json',
					color: 'lightblue',
					textColor: 'black'
				}
			],
			eventClick: function( event, jsEvent, view ) {
				if (ev.FROM != "LHCEE") {
					return false;
				}
				var erows = 4;
				var econtent = "<html><head><title>"+ev.title+"</title>";
				econtent = econtent+"</head><body><div style='background-color:whitesmoke;border-style:solid;border-width:2px;border-color:darkgray;height:25px;'>";
				econtent = econtent+"<span style='font-size:125%;vertical-align:middle;'><center>"+ev.title+"</center></span></div><span style='font-size:105%;'><br />";
				edrows = Math.floor(ev.description.length / 50) + 1;
				edrows = edrows + (ev.description.split("\n").length - 1);
				erows = erows + edrows;
				if (edrows == 1) {
					erows = erows + 2;
				}
				econtent = econtent+"<textarea cols='50' rows='"+edrows+"' id='eedesc' name='eedesc' readonly style='outline:none; resize:none; overflow: auto; font-size:105%;'>"+ev.description+"</textarea><br />";
				if (ev.allDay) {
					erows = erows + 2;
					econtent = econtent+"<br />This is an all day ev.<br />";
				}
				erows = erows + 2;
				var ds = moment(ev.bdate+"T"+ev.btime);
				econtent = econtent+"<br />Start:&nbsp;&nbsp;&nbsp;"+ds.format('dddd')+", "+ds.format('LL');
				if (ev.allDay == false) {
					econtent = econtent+" at "+ds.format('h:mm A');
				}
				econtent = econtent+"<br />";
				erows = erows + 2;
				var de = moment(ev.edate+"T"+ev.etime);
				econtent = econtent+"<br />End:&nbsp;&nbsp;&nbsp;&nbsp;"+de.format('dddd')+", "+de.format('LL');
				if (ev.allDay == false) {
					econtent = econtent+" at "+de.format('h:mm A');
				}
				econtent = econtent+"<br />";
				if (ev.location) {
					erows = erows + 2;
					econtent = econtent+"<br />Location:<br />";
					var loc = ev.location.split("|");
					var lflds = loc.length;
					var i=0;
					for (i=0; i<lflds; i++) {
						if (loc[i]) {
							erows = erows + 1;
							econtent = econtent+"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+loc[i]+"<br />";
						}
					}
				};
				if (ev.web){
					erows = erows + 2;
					econtent = econtent+"<br />Web Site:&nbsp;<a href='"+ev.web+"' target='_blank'>"+ev.web+"</a>";
				}
				if (ev.repeats == "Y") {
					econtent = econtent+"<br /><br />This event repeats";
					erows = erows + 2;

					var repdays=new Array("", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
					var repwks=new Array("", "1st", "2nd", "3rd", "4th", "Last");
					var repmths=new Array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
					var repmsg = "Every";
					var intvmsg = "";
					if (ev.interval > 1) {
						switch(ev.interval.substring(ev.interval.length - 2)) {
							case "1":
								intvmsg = " "+ev.interval+"st ";
								break;
							case "2":
								intvmsg = " "+ev.interval+"nd ";
								break;
							case "3":
								intvmsg = " "+ev.interval+"rd ";
								break;
							default:
								intvmsg = " "+ev.interval+"th ";
								break;
						}
					}
					repmsg = repmsg+intvmsg;
					switch(ev.type) {
						case "D":	
							switch(ev.daily_option) {
								case "d1":
									repmsg = repmsg+" day";
									break;
								case "d2":
									repmsg = repmsg+" weekday";
									break;
							}
							break;
						case "W":
							switch(ev.weekly_option) {
								case "w1":									
									repmsg = repmsg+" "+repdays[ev.weekly_dow]+" of every week";
									break;
							}
							break;
						case "M":
							switch(ev.monthly_option) {
								case "m1":
									repmsg = repmsg+" day "+ev.monthly_daynum+" of every month";
									break;
								case "m2":									
									repmsg = repmsg+" "+repdays[ev.monthly_dow]+" of the "+repwks[ev.monthly_wknum]+" week of every month";
									break;
							}
							break;					
						case "A":
							switch(ev.annual_option) {
								case "a1":
									repmsg = repmsg+" "+repmths[ev.annual_month1A]+" "+ev.annual_dom+" of every year";
									break;
								case "a2":
									repmsg = repmsg+" day "+repdays[ev.annual_dow]+" of the "+repwks[ev.annual_wknum]+" week of "+repmths[ev.annual_month2C]+" of every month";
									break;
								case "a3":
									repmsg = repmsg+" day "+ev.annual_daynum+" of every year";									
									break;
							}
							break;
					}
					econtent = econtent+"<br /><span style='padding-left:15px;'>"+repmsg+".</span>";
					erows = erows + 1;
					if (ev.until_date != "2099-12-31") {
						var du_yr = ev.until_date.substr(0, 4);
						var du_mo = parseInt(ev.until_date.substr(5, 2));
						var du_da = ev.until_date.substr(8, 2);
						econtent = econtent+"<br /><span style='padding-left:25px;'>Until "+repmths[du_mo]+" "+du_da+", "+du_yr+".</span>";
						erows = erows + 1;
					}
				}

				if (ev.account != 0) {
					erows = erows + 3;
					econtent = econtent+"<br /><br /><br /></span>(id="+ev.id+")";
				}
				erows = erows + 3;
				econtent = econtent+"<br /><center><input type='submit' value='Close' onclick='self.close();' /><br /></body></html>";
				erows = erows * 20;
					// Half the screen width - half popup width (450) - 10 for borders
				eleft = (window.screen.width/2) - 225 - 10;
					// Half the screen height - half the number of calculated rows - 50 for window dressing
				etop = (window.screen.height/2) - (erows / 2) - 50;	
				eventWindow=window.open('','_blank','width=450,height='+erows+',left='+eleft+',top='+etop+',location=no,menubar=no,resizable=no,scrollbar=no,titlebar=no,toolbar=no')
				eventWindow.document.write(econtent)
				eventWindow.focus()
			},
			firstDay: 0,
			ignoreTimezone: false,
			lazyFetching: true,
			loading: function(bool) {
				if (bool) $('#loading').show();
				else $('#loading').hide();
			},
			theme: true,
			weekMode: 'variable'
		});
	});

	function reloadCalendar() {
		calendar.fullCalendar('refetchEvents');
		calendar.fullCalendar('unselect');
	}
