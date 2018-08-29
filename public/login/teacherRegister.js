window.onload=setHeight;
function setHeight()
{
    var scrollBox=document.getElementById("scrollBox");
    scrollBox.style.height=window.outerHeight+"px";
    scrollBox.style.width=(window.innerWidth/3)+"px";
    var register=document.getElementById("register");
    register.style.margin="0px"+" "+"0px"+" "+"0px"+" "+(window.innerWidth/3)+"px";
}