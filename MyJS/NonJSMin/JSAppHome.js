function appHomeOpenEvent2(eId) {

	var typeFld = "EV";
	iapPrepCall("/Ajax/iapGetDB", typeFld, eId, eProcEvent);
	return false;
}

function eProcEvent(eEvent) {

	if (eEvent == 0) {
		alert("Error reading event! Sorry, nothing to show");
		return;
	} else {
		var erows = 4;
		var econtent = "<html><head><title>"+eEvent.title+"</title>";
		econtent = econtent+"</head><body><div style='background-color:whitesmoke;border-style:solid;border-width:2px;border-color:darkgray;height:25px;'>";
		econtent = econtent+"<span style='font-size:125%;vertical-align:middle;'><center>"+eEvent.title+"</center></span></div><span style='font-size:105%;'><br />";
		edrows = Math.floor(eEvent.description.length / 50) + 1;
		edrows = edrows + eEvent.description.split("\n").length - 1;
		erows = erows + edrows;
		if (edrows == 1) {
			erows = erows + 2;
		}
		econtent = econtent+"<textarea cols='50' rows='"+edrows+"' id='eedesc' name='eedesc' readonly style='outline:none; resize:none; overflow: auto; font-size:105%;'>"+eEvent.description+"</textarea><br />";
		if (eEvent.allDay) {
			erows = erows + 2;
			econtent = econtent+"<br />This is an all day eEvent.<br />";
		}
		erows = erows + 2;
		var ds = moment(eEvent.bdate+"T"+eEvent.btime);
		econtent = econtent+"<br />Start:&nbsp;&nbsp;&nbsp;"+ds.format('dddd')+", "+ds.format('LL');
		if (eEvent.allDay == false) {
			econtent = econtent+" at "+ds.format('h:mm A');
		}
		econtent = econtent+"<br />";
		erows = erows + 2;
		var de = moment(eEvent.edate+"T"+eEvent.etime);
		econtent = econtent+"<br />End:&nbsp;&nbsp;&nbsp;&nbsp;"+de.format('dddd')+", "+de.format('LL');
		if (eEvent.allDay == false) {
			econtent = econtent+" at "+de.format('h:mm A');
		}
		econtent = econtent+"<br />";
		if (eEvent.location) {
			erows = erows + 2;
			econtent = econtent+"<br />Location:<br />";
			var loc = eEvent.location.split("|");
			var lflds = loc.length;
			var i=0;
			for (i=0; i<lflds; i++) {
				if (loc[i]) {
					erows = erows + 1;
					econtent = econtent+"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+loc[i]+"<br />";
				}
			}
			erows = erows + 1;
			var a = eEvent.address;
			a = a.replace(' ', '+')
			a = a.replace('|', ',');
			econtent = econtent+"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='https://www.google.com/maps/place/"  + a + "' target='_blank'>See On The Map.</a><br>";
		};
		if (eEvent.web){
			var eweb = eEvent.web;
			eweb = eweb.toLowerCase();
			if (eweb.substr(0, 3) != "htt" ) {
				eweb = "http://" + eEvent.web;
			}
			erows = erows + 2;
			econtent = econtent+"<br />Web Site:&nbsp;<a href='"+eweb+"' target='_blank'>"+eEvent.web+"</a>";
		}
		if (eEvent.peid){
			erows = erows + 3;
			var peId = eEvent.peid;
			var peLink = "MyPages/IAPPartyEvent.php?action=selected/peid=" + peId.toString;
			econtent = econtent+"<br /><br><a href='"+peLink+"' target='_blank'>Click here to view the Party/Event details.</a><br>";

		}

		if (eEvent.repeats == "Y") {
			econtent = econtent+"<br /><br />This event repeats";
			erows = erows + 2;

			var repdays=new Array("", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
			var repwks=new Array("", "1st", "2nd", "3rd", "4th", "Last");
			var repmths=new Array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
			var repmsg = "Every";
			var intvmsg = "";
			if (eEvent.interval > 1) {
				switch(eEvent.interval.substring(eEvent.interval.length - 2)) {
					case "1":
						intvmsg = " "+eEvent.interval+"st ";
						break;
					case "2":
						intvmsg = " "+eEvent.interval+"nd ";
						break;
					case "3":
						intvmsg = " "+eEvent.interval+"rd ";
						break;
					default:
						intvmsg = " "+eEvent.interval+"th ";
						break;
				}
			}
			repmsg = repmsg+intvmsg;
			switch(eEvent.type) {
				case "D":	
					switch(eEvent.daily_option) {
						case "d1":
							repmsg = repmsg+" day";
							break;
						case "d2":
							repmsg = repmsg+" weekday";
							break;
					}
					break;
				case "W":
					switch(eEvent.weekly_option) {
						case "w1":									
							repmsg = repmsg+" "+repdays[eEvent.weekly_dow]+" of every week";
							break;
					}
					break;
				case "M":
					switch(eEvent.monthly_option) {
						case "m1":
							repmsg = repmsg+" day "+eEvent.monthly_daynum+" of every month";
							break;
						case "m2":									
							repmsg = repmsg+" "+repdays[eEvent.monthly_dow]+" of the "+repwks[eEvent.monthly_wknum]+" week of every month";
							break;
					}
					break;					
				case "A":
					switch(eEvent.annual_option) {
						case "a1":
							repmsg = repmsg+" "+repmths[eEvent.annual_month1A]+" "+eEvent.annual_dom+" of every year";
							break;
						case "a2":
							repmsg = repmsg+" day "+repdays[eEvent.annual_dow]+" of the "+repwks[eEvent.annual_wknum]+" week of "+repmths[eEvent.annual_month2C]+" of every month";
							break;
						case "a3":
							repmsg = repmsg+" day "+eEvent.annual_daynum+" of every year";									
							break;
					}
					break;
			}
			econtent = econtent+"<br /><span style='padding-left:15px;'>"+repmsg+".</span>";
			erows = erows + 1;
			if (eEvent.until_date != "2099-12-31") {
				var du_yr = eEvent.until_date.substr(0, 4);
				var du_mo = parseInt(eEvent.until_date.substr(5, 2));
				var du_da = eEvent.until_date.substr(8, 2);
				econtent = econtent+"<br /><span style='padding-left:25px;'>Until "+repmths[du_mo]+" "+du_da+", "+du_yr+".</span>";
				erows = erows + 1;
			}
		}

		if (eEvent.account != 0) {
			erows = erows + 3;
			econtent = econtent+"<br /><br /><br /></span>(id="+eEvent.id+")";
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
	}
}
