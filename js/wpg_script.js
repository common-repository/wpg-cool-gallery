function hideandshowOption(data)
{
	if(data.value=="image")
	{
		
		document.getElementById("wpg_link_field").style.display = 'none';
		document.getElementById("wpg_image_field").style.display = 'block';		
		document.getElementById("wpg_image_field_image").style.display = 'block';
	}
	else if(data.value=="video_link")
	{
		document.getElementById("wpg_link_field").style.display = 'block';
		document.getElementById("wpg_image_field").style.display = 'none';
		document.getElementById("wpg_image_field_image").style.display = 'none';
		
	}
	else
	{
		document.getElementById("wpg_link_field").style.display = 'none';
		document.getElementById("wpg_image_field").style.display = 'none';
		document.getElementById("wpg_image_field_image").style.display = 'none';
		
	}	
}
