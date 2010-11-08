function ObjFromID(id)
{
	if(document.getElementById) 
		return document.getElementById(id);
	else if(document.all) 
		return document.all[id]; 
	else if(document.layers) 
		return document.layers[id];
}

function CheckCount()
{
  var item = ObjFromID('count');
  var val = item.value;
  if(!IsInteger(val) || (val <= 0)) 
  {
    alert('Please enter a positive whole number for item quantity.');
    item.select();
    item.focus();
    return false;
  }
  
  return true;
}

function CheckNumber(obj)
{
  if(IsInteger(obj.value) && (obj.value > 0))
  {
    obj.style.borderBottomColor = obj.style.borderTopColor;
  }
  else
  {
    obj.style.borderBottomColor = '#f00';
  }
}

function IsInteger(x) 
{
  var RegExp = /^(\d*)$/;
  return (x.match(RegExp));
}
