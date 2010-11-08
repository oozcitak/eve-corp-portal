function ObjFromID(id)
{
	if(document.getElementById) 
		return document.getElementById(id);
	else if(document.all) 
		return document.all[id]; 
	else if(document.layers) 
		return document.layers[id];
}

function CheckPrice()
{
  var item = ObjFromID('price');
  var val = item.value;
  if(!IsInteger(val)) 
  {
    alert('Please enter a whole number for item price.');
    item.select();
    item.focus();
    return false;
  }
  
  return true;
}

function CheckNumber(obj)
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

function ajaxPrice(obj)
{
  var item = obj.options[obj.selectedIndex].value;
  var url = './breakdown.xml.php?item=' + item;
  var request = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("MSXML2.XMLHTTP.3.0");
  request.open("GET", url, true);

  request.onreadystatechange = function()
  {
    if (request.readyState == 4 && request.status == 200)
    {
      if (request.responseXML)
      {
        var xmlDoc = request.responseXML.documentElement;
        var total = xmlDoc.childNodes[3].childNodes[0].nodeValue;
        var text = '<table class="data">';
        text += '<tr><th colspan="2">Breakdown</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr>';
        for(var i = 0; i < xmlDoc.childNodes.length; i++)
        {
          var mat = xmlDoc.childNodes[i];
          if(mat.nodeName == "Material")
          {
            var name, icon, quantity, unit, cost;
            for(var j = 0; j < mat.childNodes.length; j++)
            {
              var att = mat.childNodes[j];
              if(att.nodeName == "Name") name = att.childNodes[0].nodeValue;
              if(att.nodeName == "Icon") icon = att.childNodes[0].nodeValue;
              if(att.nodeName == "Quantity") quantity = att.childNodes[0].nodeValue;
              if(att.nodeName == "UnitPrice") unit = att.childNodes[0].nodeValue;
              if(att.nodeName == "TotalPrice") cost = att.childNodes[0].nodeValue;
            }
            text += '<tr>';
            text += '<td><img src="../../eveicons/32_32/icon' + icon + '.png" width="32" height="32" /></td>';
            text += '<td style="vertical-align: middle">' + name + '</td>';
            text += '<td style="vertical-align: middle; text-align: right;">' + quantity + '</td>';
            text += '<td style="vertical-align: middle; text-align: right;">' + (unit == 0 ? '???' : unit + ' ISK') + '</td>';
            text += '<td style="vertical-align: middle; text-align: right;">' + (unit == 0 ? '???' : cost + ' ISK') + '</td>';
            text += '</tr>';
          }
        }
        text += '<tr><th colspan="4">Item Price Based On Breakdown</th><th>' + total + ' ISK</th></tr>';
        text += '</table>';
        
      	var el = ObjFromID('price_placeholder');
        el.className = '';
        el.innerHTML = text;
      }
    }
    else
    {
      var el = ObjFromID('price_placeholder');
      el.className = 'throbber';
      el.innerHTML = ' ';
    }
  };
  request.send(null);
}
