$(function () {
	var topicID;
	var profileID;
	getProfile();
	//getTopicList();
	setPusher();
	setSubmit()
});

function setSubmit(){
    $('#messageForm').submit(function(event) {
    	event.preventDefault();
		sendMessage();
	});
}

function getProfile(){
	var data = new Object();
	data.type = "profile";
	$.ajax({
		dataType: "json",
		type: "GET",
		url: BASE_URL + "TTUtils.php",
		data: data,
		success: function(res){
			var status = res["status"];
			if(status == 0){
				profileID = res["id"];
				getTopicList();
			}else{
				location.href="Authorize.php";
			}
		}
	});
	return false;
}

function getTopicList(){
	var data = new Object();
	data.type = "topicsList";
	$.ajax({
		dataType: "json",
		type: "GET",
		url: BASE_URL + "TTUtils.php",
		data: data,
		success: function(res){
			var status = res["status"];
			if(status == "0"){
				var array = new Array();
				$.extend(true, array, res['topics']);
				var len = array.length;
				var pulldown = new String();
				pulldown += "<select name='topic_id' onChange='selectTopic(this)'>";
				for(var i = 0;i < len;i++){
					pulldown += "<option value='" + array[i].topic.id + "'>" + array[i].topic.name + "</option>";
				}
				pulldown += "</select>";
				$("#list").append(pulldown);

				topicID = array[0].topic.id;
				getTopic(topicID);
			}else{
				location.href="Authorize.php";
			}
		}
	});
	return false;
}

function selectTopic(obj){
	topicID = obj.options[obj.selectedIndex].value;
	getTopic(topicID, null, null, null);
	return false;
}

function getTopic(topicId, from, count, direction){
	$("#timeline").empty();
	var data = new Object();
	data.type = "topic";
	data.id = topicId;
	if(from != null){data.from = from;}
	if(count != null){data.count = count;}
	if(direction != null){data.direction = direction;}
	$.ajax({
		dataType: "json",
		type: "GET",
		url: BASE_URL + "TTUtils.php",
		data: data,
		success: function(res){
			var status = res['status'];
			if(status == "0"){
				var array = new Array();
				$.extend(true, array, res['posts']);
				var len = array.length;
				var list = new String();
				list = "<div id=posts>";
				for(var i = len - 1;i >= 0 ;i--){
					var originMessage = array[i]['message'];
					var message = new String();
					var urlList = get_urllist(originMessage);
					var urlListLen = urlList.length;
					for(var j = 0;j < urlListLen;j++){
						var url = urlList[j];
						message += "<img width='100%' src='" + url + "'>";
					}
					originMessage = originMessage.replace(/(https?:\/\/[\x21-\x7e]+)/g, "");
					originMessage += message;
					var postId = array[i]['id'];
					var name = array[i]['account']['name'];
					var userId = array[i]['account']['id'];
					var icon = array[i]['account']['imageUrl'];
					var likes = array[i]['likes'].length;
					var likesArray = new Array();
					var likeClassName = "like";
					$.extend(true, likesArray, array[i]['likes']);
					for(var k = 0;k < likes;k++){
						if(likesArray[k].account.id == profileID){
							likeClassName = "checkLike";
						}
					}
					var post = new String();
					post = "<div id='post'>";
					post += "<p>【massege】"+originMessage+"</p>";
					post += "<p>【ikes】</p><p class='" + likeClassName + "' id='" + postId + "'>"+likes+"</p>";
					post += "<p>【ame】"+name+"</p>";
					post += "</p>【id】"+postId+"</p>";
					post += "<img src='"+icon+"'>";
					post += "</div>";
					post += "<hr>";
					list += post;
				}
				list += "</div>";
				$("#timeline").append(list);
				pushLike();
			}else{
				location.href="Authorize.php";
			}
		}
	})
	return false;
}
function sendMessage(){
	var data = new Object();
	data.id = topicID;
	data.type = "sendMessage";
	data.message = $('#inputMessage').val();
	//alert($('#inputMessage').val());
	$.ajax({
		dataType: "json",
		type: "GET",
		url: BASE_URL + "TTUtils.php",
		data: data,
		success: function(res){
			console.log(res);
			if(res['status'] == 0){
				getTopic(topicID, null, null, null);
				$('#inputMessage').val('');
			}else{
				location.href="Authorize.php";
			}
		}
	})
}
function pushLike(){
	$('.like').click(function(event){
		var data = new Object();
		data.topicId = topicID;
		data.type = "like";
		data.postId = $(this).attr('id');
		var me = $(this);
		$.ajax({
			dataType: "json",
			type: "GET",
			url: BASE_URL + "TTUtils.php",
			data: data,
			success: function(res){
				if(res['status'] == 0){
					var num = Number(me.text());
					num++;
					me.text(num);
					me.removeClass('like');
					me.addClass('checkLike');
					me.unbind();
				}else{
					location.href="Authorize.php";
				}
			}
		})
	})
}
function setPusher(){
	Pusher.log = function(message) {
      if (window.console && window.console.log) {
        window.console.log(message);
      }
    };
    var pusher = new Pusher(PUSHER_KEY);
    var channel = pusher.subscribe('tt-channel');
    channel.bind('tt-event', function(data) {
    	getTopic(data);
    });
}

function get_urllist(str){
	var pat=/(https?:\/\/[\x21-\x7e]+)/g;
	var list=str.match(pat);
	if(!list)return [];
	return list;
}