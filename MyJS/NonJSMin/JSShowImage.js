function siShowImage() {

	var siSupplier = document.getElementById("IAPSUPPID").value;
	var siImageFile = document.getElementById("IAPCATIMG").value;
	var siPath = document.getElementById("IAPURL").value;
	var siImagePath = siPath + "/Supp" + siSupplier + "Images/" + siImageFile;

	var siRows = 200;
	var siContents = "<html><head><title>Item Image </title></head><body><center><img src='" + siImagePath +"' width='200' height='200'></center></body></html>";
// Half the screen width - half popup width (250) - 10 for borders
	var siLeft = (window.screen.width/2) - 250 - 10;
// Half the screen height - half the number of calculated rows - 50 for window dressing
	var siTop = (window.screen.height/2) - (siRows / 2) - 50;
	var siHeight = siRows + 10;
	var siWindow = window.open('','_blank','width=255,height='+siHeight+',left='+siLeft+',top='+siTop+',location=no,menubar=no,resizable=no,scrollbar=no,titlebar=no,toolbar=no');
	siWindow.document.write(siContents);
	siWindow.focus();	
}