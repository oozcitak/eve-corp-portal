function GetName()
{
  var el = ObjFromID('names');
  var id = el.selectedIndex;
  var opt = el.options[id];
  var val = opt.value;
  var text = opt.text;
  return [val, text];
}

function AddTo()
{
  Add('to');
}

function AddCC()
{
  Add('cc');
}

function AddBCC()
{
  Add('bcc');
}

function ClearTo()
{
  Clear('to');
}

function ClearCC()
{
  Clear('cc');
}

function ClearBCC()
{
  Clear('bcc');
}

function Add(id)
{
  var sel = GetName();
  var el = ObjFromID(id);
  var elid = ObjFromID(id + 'id');
  
  if(el.value == '')
  {
    el.value = sel[1];
    elid.value = sel[0];
  }
  else
  {
    el.value = StringFilter(el.value + ',' + sel[1]);
    elid.value = StringFilter(elid.value + ',' + sel[0]);
  }
}

function Clear(id)
{
  var el = ObjFromID(id);
  var elid = ObjFromID(id + 'id');
  
  el.value = '';
  elid.value = '';
}

function StringFilter(str)
{
  var res = [];
  var arr = str.split(',');
  
  for (var i = 0; i < arr.length; i++) 
  {
    if(res.indexOf(arr[i]) == -1)
      res.push(arr[i]);
  }
  
  return res.join(',');
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

function SelectAll()
{
 Select(true);
}

function SelectNone()
{
  Select(false);
}

function Select(checked)
{
  var allinputs = document.getElementsByTagName("input");
  
  var i = 0;
  var input;
  for(i = 0; i < allinputs.length; i++)
  {
    input = allinputs[i];
    if((input.type == "checkbox") && (input.name.substr(0, 8) == "mailitem"))
    {
      input.checked = checked;
    }
  }
}
