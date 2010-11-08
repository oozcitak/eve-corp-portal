function CheckNumber(obj)
{
  if(obj.value == '' || IsNumeric(obj.value))
  {
    obj.style.borderBottomColor = obj.style.borderTopColor;
  }
  else
  {
    obj.style.borderBottomColor = '#f00';
  }
}

function UpdateRefiningSkill()
{
	var ref = ObjFromID('refining');
	var eff = ObjFromID('refinery_efficiency');

  if(ref.selectedIndex != 5)
    eff.selectedIndex = 0;
}

function UpdateRefineryEfficienySkill()
{
	var ref = ObjFromID('refining');
	var eff = ObjFromID('refinery_efficiency');

  if(eff.selectedIndex != 0)
    ref.selectedIndex = 5;
}

function IsNumeric(x) 
{
  var RegExp = /^(-)?(\d*)(\.?)(\d*)$/;
  return (x.match(RegExp));
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
