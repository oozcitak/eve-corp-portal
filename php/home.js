var CountDown;
var TQCountDown;

function InitSkillTimer(seconds)
{
  if(seconds == 0) return;
  CountDown = seconds;
  UpdateSkillTimer();
}

function InitTranqTimer(seconds)
{
  if(seconds == 0) return;
  TQCountDown = seconds;
  UpdateTranqTimer();
}

function UpdateSkillTimer()
{
  CountDown = CountDown - 1;

  var els = ObjFromID('sit_timer');
  var elp = ObjFromID('sit_main');

  if(CountDown > 0 && (els))
  {
    els.innerHTML = SecondsToTime(CountDown);
    setTimeout('UpdateSkillTimer()', 1000);
  }
  else if(elp)
  {			
    elp.innerHTML = "Skill training completed!";
  }
}

function UpdateTranqTimer()
{
  TQCountDown = TQCountDown - 1;
  var els = ObjFromID('tranq_timer');
  var elp = ObjFromID('tranq_main');

  if(TQCountDown > 0 && (els))
  {
    els.innerHTML = SecondsToTime(TQCountDown);
    setTimeout('UpdateTranqTimer()', 1000);
  }
  else if(elp)
  {			
    elp.innerHTML = "Tranquility Server Online";
  }
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

function SecondsToTime(seconds)
{
  if(seconds <= 0) return "";

  var days = Math.floor(seconds / 86400);
  var hours = Math.floor(seconds / 3600) - days * 24;
  var mins = Math.floor(seconds / 60) - hours * 60 - days * 1440;
  var secs = Math.floor(seconds - mins * 60 - hours * 3600 - days * 86400);

  var str = "";
  if(days) str += days+"D ";
  if(hours) str += hours+"H ";
  if(mins) str += mins+"M ";
  if(secs) str += secs+"S ";

  return str;
}

function ajaxTraining(server, training, evenews, devblogs, rpnews)
{
  if(training == 0)
  {
    ajaxServer(server, training, evenews, devblogs, rpnews)
    return;
  }
  
  var url = './xml/training.xml.php';
  var request = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("MSXML2.XMLHTTP.3.0");
  request.open("GET", url, true);

  request.onreadystatechange = function()
  {
    if (request.readyState == 4 && request.status == 200)
    {
      if (request.responseXML)
      {
        var xmlDoc = request.responseXML.documentElement;

        // Skill in training
        var trainingstatus = '';
        var seconds = 0;
        if(xmlDoc.getElementsByTagName("error")[0].childNodes[0])
        {
          trainingstatus = "<p>" + xmlDoc.getElementsByTagName("error")[0].childNodes[0].nodeValue + "</p>";
        }
        else if(xmlDoc.getElementsByTagName("training")[0].childNodes[0].nodeValue == 0)
        {
          if(xmlDoc.getElementsByTagName("cacheduntil")[0].childNodes[0])
            trainingstatus = "<p>There is no skill in training!</p><p><span class='info'>(Skill training information is cached for 15 minutes. Next update on " + xmlDoc.getElementsByTagName("cacheduntil")[0].childNodes[0].nodeValue + ").</span></p>";
          else
            trainingstatus = "<p>There is no skill in training!</p>";
        }
        else
        {
          var romans = ["I", "II", "III", "IV", "V"];
          var tolevel = xmlDoc.getElementsByTagName("tolevel")[0].childNodes[0].nodeValue;
          var secondsleft = xmlDoc.getElementsByTagName("secondsleft")[0].childNodes[0].nodeValue;
          var skillname = xmlDoc.getElementsByTagName("skillname")[0].childNodes[0].nodeValue;
          var endtime = xmlDoc.getElementsByTagName("endtime")[0].childNodes[0].nodeValue;
          var cacheduntil = xmlDoc.getElementsByTagName("cacheduntil")[0].childNodes[0].nodeValue;

          trainingstatus = "<p id='sit_main'>";
          trainingstatus += skillname + " " + romans[tolevel - 1] + "<br />";
          trainingstatus += "<span id='sit_timer'>" + SecondsToTime(secondsleft) + "</span>";
          trainingstatus += "&nbsp;(" + endtime + ")</p><p><img src='../img/level" + tolevel + "_act.gif' /></p>";
          trainingstatus += "<p><span class='info'>(Cached until " + cacheduntil + ")</span></p>";
          InitSkillTimer(secondsleft);
        }
        
        var text = '';
        if((server == 1) && (training == 1))
          text = "<hr size='0' />" + trainingstatus;
        else
          text = trainingstatus;
          
      	var el = ObjFromID('sit_placeholder');
        el.className = '';
        el.innerHTML = text;
      }
      ajaxServer(server, training, evenews, devblogs, rpnews)
    }
  };  
  request.send(null);
}

function ajaxServer(server, training, evenews, devblogs, rpnews)
{
  if(server == 0)
  {
    ajaxReadEveNews(evenews, devblogs, rpnews)
    return;
  }
  
  var url = './xml/serverstatus.xml.php';
  var request = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("MSXML2.XMLHTTP.3.0");
  request.open("GET", url, true);

  request.onreadystatechange = function()
  {
    if (request.readyState == 4 && request.status == 200)
    {
      if (request.responseXML)
      {
        var xmlDoc = request.responseXML.documentElement;
        
        // Tranquility
        var tranqstatus = '<p>Tranquility Server Status Unknown</p>';
        if(xmlDoc.getElementsByTagName("status")[0].childNodes[0])
        {
          var status = xmlDoc.getElementsByTagName("status")[0].childNodes[0].nodeValue;
          var data = xmlDoc.getElementsByTagName("data")[0].childNodes[0].nodeValue;
          var time = xmlDoc.getElementsByTagName("time")[0].childNodes[0].nodeValue;
          var motd = '';
          if(xmlDoc.getElementsByTagName("motd")[0].childNodes[0])
            motd = xmlDoc.getElementsByTagName("motd")[0].childNodes[0].nodeValue;
          if(status == 1)
          {
            tranqstatus = "<p>Tranquility Server Offline</p>";
          }
          else if(status == 2)
          {
            tranqstatus = "<p>Tranquility Server Online (" + data + " pilots)</p>";
            tranqstatus += "<p>Current Server Time:&nbsp;" + time + "</p>";    
            if(motd) tranqstatus += "<p>" + motd + "</p>";
          }
          else if(status == 3)
          {
            tranqstatus = "<p id='tranq_main'>Tranquility is starting up (<span id='tranq_timer'>" + SecondsToTime(data) + "</span>)</p>";
            tranqstatus += "<p>Current Server Time:&nbsp;" + time + "</p>";    
            if(motd) tranqstatus += "<p>" + motd + "</p>";
            InitTranqTimer(data);
          }
        }
                  
      	var el = ObjFromID('server_placeholder');
        el.className = '';
        el.innerHTML = tranqstatus;
      }
      ajaxReadEveNews(evenews, devblogs, rpnews)
    }
  };  
  request.send(null);
}

function ajaxReadEveNews(evenews, devblogs, rpnews)
{
  if(evenews == 0)
  {
    ajaxReadDevBlogs(devblogs, rpnews)
    return;
  }
  
  var url = './xml/evenews.xml.php';
  var vars = 'feed=evenews';
  var request = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("MSXML2.XMLHTTP.3.0");
  request.open("GET", url + "?" + vars, true);

  request.onreadystatechange = function()
  {
    if (request.readyState == 4 && request.status == 200)
    {
      if (request.responseXML)
      {
        var xmlDoc = request.responseXML.documentElement;
        
        var text = '';
        for(var i = 0; i < 5; i++)
        {
          if(xmlDoc.getElementsByTagName("item")[i].childNodes[0])
          {
            var item = xmlDoc.getElementsByTagName("item")[i];
            text += "<p><a href='" + item.childNodes[1].childNodes[0].nodeValue + "'>" + item.childNodes[0].childNodes[0].nodeValue + "</a><br />";
            text += item.childNodes[2].childNodes[0].nodeValue + "</p>";
          }
        }
                  
      	var el = ObjFromID('evenews_placeholder');
        el.className = '';
        el.innerHTML = text;
      }
      ajaxReadDevBlogs(devblogs, rpnews)
    }
  };  
  request.send(null);
}

function ajaxReadDevBlogs(devblogs, rpnews)
{
  if(devblogs == 0)
  {
    ajaxReadRPNews(rpnews)
    return;
  }
  
  var url = './xml/evenews.xml.php';
  var vars = 'feed=devblogs';
  var request = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("MSXML2.XMLHTTP.3.0");
  request.open("GET", url + "?" + vars, true);

  request.onreadystatechange = function()
  {
    if (request.readyState == 4 && request.status == 200)
    {
      if (request.responseXML)
      {
        var xmlDoc = request.responseXML.documentElement;
        
        var text = '';
        for(var i = 0; i < 5; i++)
        {
          if(xmlDoc.getElementsByTagName("item")[i].childNodes[0])
          {
            var item = xmlDoc.getElementsByTagName("item")[i];
            text += "<p><a href='" + item.childNodes[1].childNodes[0].nodeValue + "'>" + item.childNodes[0].childNodes[0].nodeValue + "</a><br />";
            text += item.childNodes[2].childNodes[0].nodeValue + "</p>";
          }
        }
        
      	var el = ObjFromID('devblogs_placeholder');
        el.className = '';
        el.innerHTML = text;
      }
      ajaxReadRPNews(rpnews)
    }
  };  
  request.send(null);
}

function ajaxReadRPNews(rpnews)
{
  if(rpnews == 0)
  {
    return;
  }
  
  var url = './xml/evenews.xml.php';
  var vars = 'feed=rpnews';
  var request = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("MSXML2.XMLHTTP.3.0");
  request.open("GET", url + "?" + vars, true);

  request.onreadystatechange = function()
  {
    if (request.readyState == 4 && request.status == 200)
    {
      if (request.responseXML)
      {
        var xmlDoc = request.responseXML.documentElement;
        
        var text = '';
        for(var i = 0; i < 5; i++)
        {
          if(xmlDoc.getElementsByTagName("item")[i].childNodes[0])
          {
            var item = xmlDoc.getElementsByTagName("item")[i];
            text += "<p><a href='" + item.childNodes[1].childNodes[0].nodeValue + "'>" + item.childNodes[0].childNodes[0].nodeValue + "</a><br />";
            text += item.childNodes[2].childNodes[0].nodeValue + "</p>";
          }
        }
        
      	var el = ObjFromID('rpnews_placeholder');
        el.className = '';
        el.innerHTML = text;
      }
    }
  };  
  request.send(null);
}
