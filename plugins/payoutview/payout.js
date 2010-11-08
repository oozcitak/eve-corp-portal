function AddPlayer(id, name)
{
  if(arguments.length == 0)
  {
    var list = ObjFromID('names');
    var item = list.options[list.selectedIndex];
    id = item.value;
    name = item.text;
  }
  else
  {
    name = unescape(name);
  }
  
  // Does the player already exist?
  if(ObjFromID('row' + id))
    return;
  
  // Placeholder
  var holder = ObjFromID('placeholder').childNodes[1];
  ObjFromID('headerrow').style.display = '';
  var rowcount = holder.childNodes.length;
  
  // Row
  var row = document.createElement('tr');
  row.setAttribute('id', 'row' + id);
  row.setAttribute('class', (IsEven(rowcount) ? 'altrow1' : 'altrow2'));
  holder.appendChild(row);
  
  // Name
  var cell = document.createElement('td');
  var eltext = document.createTextNode(name);
  cell.appendChild(eltext);
  row.appendChild(cell);
  
  // Time in
  var cell = document.createElement('td');
  var el = document.createElement('input');
  el.setAttribute('type', 'text');
  el.setAttribute('size', '10');
  el.setAttribute('value', '09:00');
  el.setAttribute('name', 'timein' + id);
  el.setAttribute('id', 'timein' + id);
  el.setAttribute('onblur', 'javascript:CheckTime(this);');  
  cell.appendChild(el);
  row.appendChild(cell);
  
  // Time out
  var cell = document.createElement('td');
  var el = document.createElement('input');
  el.setAttribute('type', 'text');
  el.setAttribute('size', '10');
  el.setAttribute('value', '10:00');
  el.setAttribute('name', 'timeout' + id);
  el.setAttribute('id', 'timeout' + id);
  el.setAttribute('onblur', 'javascript:CheckTime(this);');  
  cell.appendChild(el);
  row.appendChild(cell);
  
  // Remove link
  var cell = document.createElement('td');
  var el = document.createElement('a');
  el.setAttribute('id', 'remove' + id);
  el.setAttribute('href', '#');
  el.setAttribute('onclick', 'javascript:RemovePlayer(' + id + ');return false;');
  var eltext = document.createTextNode('  Remove');
  el.appendChild(eltext);
  cell.appendChild(el);
  row.appendChild(cell);
}

function RemovePlayer(id)
{
  var holder = ObjFromID('placeholder').childNodes[1];
  var row = ObjFromID('row' + id);
  holder.removeChild(row);
  var rowcount = holder.childNodes.length;

  if(rowcount == 2)
    ObjFromID('headerrow').style.display = 'none';
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

function CheckTime(obj)
{
  if(IsTime(obj.value))
  {
    obj.style.borderBottomColor = obj.style.borderTopColor;
  }
  else
  {
    obj.style.borderBottomColor = '#f00';
  }
}

function CheckAll()
{
  if(!CheckAllTimes()) return false;
  if(!CheckAllItems()) return false;
  
  return true;
}

function CheckAllTimes()
{
  var items = document.getElementsByTagName('input');
  for(var i = 0; i < items.length; i++)
  {
    var item = items[i];
    if((item.id.substring(0, 6) == 'timein') || (item.id.substring(0, 7) == 'timeout'))
    {
      var val = item.value;
      if(!IsTime(val)) 
      {
        alert('Please enter all times as hh:mm.');
        item.select();
        item.focus();
        return false;
      }
    }
  }
  
  return true;
}

function CheckAllItems()
{
  var items = document.getElementsByTagName('input');
  for(var i = 0; i < items.length; i++)
  {
    var item = items[i];
    if(item.name.substring(0, 4) == 'item')
    {
      var val = item.value;
      if(!IsInteger(val)) 
      {
        alert('Please enter whole numbers for item quantities.');
        item.select();
        item.focus();
        return false;
      }
    }
  }
  
  return true;
}

function CheckInteger(obj)
{
  if(obj.value == '' || IsInteger(obj.value))
  {
    obj.style.borderBottomColor = obj.style.borderTopColor;
  }
  else
  {
    obj.style.borderBottomColor = '#f00';
  }
}

function IsTime(x) 
{
  var RegExp = /^(\d?)(\d)(:)(\d)(\d)$/;
  return (x.match(RegExp));
}

function IsEven(x)
{
  return (Math.floor(x / 2) == x / 2);
}

function IsInteger(x) 
{
  var RegExp = /^(\d*)$/;
  return (x.match(RegExp));
}
