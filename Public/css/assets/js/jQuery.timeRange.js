
$(function(){
	$('.timeRange').click(function(){
		$('#timeRange_div').remove();
		
		var hourOpts = '';
		var hourOpts2='';
		var minuteOpts = '';
		for (i=0;i<24;i++){
			if(i<10){
				hourOpts += '<option>'+"0"+i+'</option>';
			}else{
				hourOpts += '<option>'+i+'</option>';
			}
		}


		for (i=0;i<60;i++){
			if(i<10){
				minuteOpts += '<option>'+"0"+i+'</option>';
			}else{
				minuteOpts += '<option>'+i+'</option>';
			}
		}
		//var minuteOpts = '<option>00</option><option>10</option><option>20</option><option>30</option><option>40</option><option>50</option>';
		
		var html = $('<div id="timeRange_div"><select id="timeRange_a" style="width: 50px;margin: 5px">'+hourOpts+
			'</select>:<select id="timeRange_b" style="width: 50px;margin: 5px">'+minuteOpts+
			'</select>-<select id="timeRange_c" style="width: 50px;margin: 5px">'+hourOpts+
			'</select>:<select id="timeRange_d" style="width: 50px;margin: 5px">'+minuteOpts+
			'</select><input type="button" value="确定" id="timeRange_btn" style="width: 50px;margin: 5px" /></div>')
			.css({
				"width":"324px",
				"position": "absolute",
				"left":"100px",
				"z-index": "999",
				"padding": "5px",
				//"padding-left":"20p"
				"border": "1px solid #AAA",
				"background-color": "#FFF",
				"box-shadow": "1px 1px 3px rgba(0,0,0,.4)"
			})
			.click(function(){return false});

		/*$('#timeRange_a').change(function(){
			alert(1);
		})*/
		/*$('#timeRange_a').on('change',function(){
			alert(1);
			console.log($(this).html())
			//$(this).html()
		})*/
		// 如果文本框有值
		var v = $(this).val();
		if (v) {
			v = v.split(/:|-/);
			html.find('#timeRange_a').val(v[0]);
			html.find('#timeRange_b').val(v[1]);
			html.find('#timeRange_c').val(v[2]);
			html.find('#timeRange_d').val(v[3]);
		}
		/*html.find('#timeRange_a').change(function(){
			for (i=(+html.find('#timeRange_a').val());i<24;i++){
				if(i<10){
					hourOpts2 += '<option>'+"0"+i+'</option>';
				}else{
					hourOpts2 += '<option>'+i+'</option>';
				}
			}
			html.find('#timeRange_c').empty().append(hourOpts2)

			//alert(html.find('#timeRange_a').val())
		})*/
		// 点击确定的时候
		var pObj = $(this);
		html.find('#timeRange_btn').click(function(){
			//if()
				var timeRange_a= html.find('#timeRange_a').val()
				var timeRange_b= html.find('#timeRange_b').val()
				var timeRange_c= html.find('#timeRange_c').val()
				var timeRange_d= html.find('#timeRange_d').val()
			if(timeRange_a+'timeRange_b'>timeRange_c+'timeRange_d'){
				layer.alert('请输入正确时间')
				//confirm(1);
				return;
			}
			var str = timeRange_a+':'
			 +timeRange_b+'-'
			 +timeRange_c+':'
			 +timeRange_d;

			/*var str = html.find('#timeRange_a').val()+':'
				+html.find('#timeRange_b').val()+'-'
				+html.find('#timeRange_c').val()+':'
				+html.find('#timeRange_d').val();*/
			pObj.val(str);

			$('#timeRange_div').remove();
		});

		$(this).after(html);
		return false;
	});

	$(document).click(function(){
		$('#timeRange_div').remove();
	});

});