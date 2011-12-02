var listPrint =  (function(object)
{
	
	var recurse = function(ob,last)
	{
		if (last === undefined)
		{
			last = true;
		}
		
		switch (typeof ob)
		{
			case 'object':
				if (ob instanceof Array)
				{
					return print_array(ob,last);
				} else
				{
					return print_object(ob,last);
				}
				break;
			case 'number':
					return print_number(ob,last);
				break;
			case 'string':
			default:
					return print_string(ob,last);
				break;
		}
	}
	
	var print_array = function(input,last)
	{
		
		var print = $("<div>");
		$("<span class=square_start>[</span><ul class=array></ul><span class=square_end>]"+(last ? '' : ',')+"</span>").appendTo(print);
		var ul = print.find("ul");
		
		for (var i=0; i < input.length; i++) {
			var bit = $("<li>");
			bit.html("<span style='color: grey' class=toggler>"+i+":</span> ").append(recurse(input[i],i==(input.length-1)));
			ul.append(bit);
		}
		
		return print.html();
	}
	
	var print_object = function(input,last)
	{
		
		var print = $("<div>");
		$("<span class=curly_start>{</span><ul class=object></ul><span class=curly_end>}"+(last ? '' : ',')+"</span>").appendTo(print);
		var ul = print.find("ul");
		
		var length 	= object_length(input);
		var x 		= 0;
		
		for (var i in input) {
			if (!input.hasOwnProperty(i)) continue;
			var bit = $("<li>");
			bit.html("<span class=toggler>"+i+":</span> ").append(recurse(input[i],x==(length-1)));
			ul.append(bit);
			x++;
		}
		
		return print.html();
	}
	
	var print_number = function(input,last)
	{
		return "<span class=number>"+input+"</span>"+(last ? '' : ',');
	}
	
	var print_string = function(input,last)
	{
		return "<span class=string>"+JSON.stringify(input)+"</span>"+(last ? '' : ',');
	}
	
	var object_length = function(input)
	{
		var length = 0;
		for (var i in input)
		{
			if (!input.hasOwnProperty(i)) continue;
			length++;
		}
		return length;
	}
	
	var output = $(recurse(object));
	
	output.find(".toggler").each(function() {
		$(this).click(function(e) {
			$(this).parent().children("ul").toggle();
		});
	});
	
	output.find("li").each(function() {
		$(this).mouseover(function(e) {
			if (e.target != this) return;
			$(this).addClass('hover');
		});
		$(this).mouseout(function(e) {
			$(this).removeClass('hover');
		});
	});
	
	return $("<div class='listprint'>").html(output);
	
});