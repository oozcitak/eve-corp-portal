function SelectByTimeZone(id)
{
  var curdate = new Date();
  var offset = (curdate.getTimezoneOffset() / 60) * -1;
  
  offset = offset + 12;
  if(offset > 25) offset = 25;
  if(offset < 0) offset = 0;
  
  var els = ObjFromID(id);
  els.selectedIndex = offset;
}

function ObjFromID(id)
{
	if(document.getElementById) 
		return document.getElementById(id);
	else if(document.all) 
		return document.all[id]; 
	else if(document.layers) 
		return document.layers[id];
}
