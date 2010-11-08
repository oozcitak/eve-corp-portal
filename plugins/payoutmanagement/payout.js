function SelectAll()
{
  var items = document.getElementsByTagName('input');
  for(var i = 0; i < items.length; i++)
  {
    var item = items[i];
    if(item.id.substring(0, 2) == 'op')
      item.checked = true;
  }  
}

function SelectNone()
{
  var items = document.getElementsByTagName('input');
  for(var i = 0; i < items.length; i++)
  {
    var item = items[i];
    if(item.id.substring(0, 2) == 'op')
      item.checked = false;
  }  
}

function ShowReject()
{
  ObjFromID('reject').style.display = '';
  ObjFromID('buttons').style.display = 'none';
  window.scrollTo(0, 10000);
}

function HideReject()
{
  ObjFromID('reject').style.display = 'none';
  ObjFromID('buttons').style.display = '';
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

function CheckAll()
{
  var corpcut = ObjFromID('corpcut');
  
  if(!IsInteger(corpcut.value)) 
  {
    alert('Please enter an integer for corporation cut.');
    corpcut.select();
    corpcut.focus();
    return false;
  }

  return check;
}

function CheckInteger(obj)
{
  if(IsInteger(obj.value))
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
