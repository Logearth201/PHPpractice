$("#expand_area").height(100);//init
$("#expand_area").css("lineHeight","20px");//init

/*
	‰Šúˆ—
*/
if(evt.target.scrollHeight > evt.target.offsetHeight){   
	$(evt.target).height(evt.target.scrollHeight);
}else{          
	var lineHeight = Number($(evt.target).css("lineHeight").split("px")[0]);
	while (true){
		$(evt.target).height($(evt.target).height() - lineHeight); 
		if(evt.target.scrollHeight > evt.target.offsetHeight){
			$(evt.target).height(evt.target.scrollHeight);
			break;
		}
	}
}

function exp_textarea(){
	$("#expand_area").height($("#expand_area").height()+145);
}