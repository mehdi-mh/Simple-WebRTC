<script src="SIPml-api.js"></script>

<table>

	<tr>
		<td>Display Name:</td>
		<td><input type='text' id='display_name' value='Mehdi Hosseini'></td>
	</tr>
	<tr>
		<td>SIP extension</td>
		<td><input type='text' id='sip' value='1-125'></td>
	</tr>
	<tr>
		<td>Public Identity</td>
		<td><input type='text' id='public_identity' value='sip:1-125@192.168.2.1'></td>
	</tr>
	<tr>
		<td>Password</td>
		<td><input type='text' id='password' value=''></td>
	</tr>
	<tr>
		<td>Realm</td>
		<td><input type='text' id='realm' value='192.168.2.1'></td>
	</tr>
	<tr>
		<td><input type='button' value='Register' onclick='do_login();'></td>
		<td><input type='button' value='UnRegister' onclick='do_logout();'></td>
	</tr>
</table>

<div id="content">

</div>
<hr />
Call:
<input type='button' value='Accept Call' onclick='do_accept();' id='btn_accept' style='display:none'>
<input type='text' value='' id='number'>
<input type='button' value='Dial' onclick='do_call();' id='btn_accept'>

<script>
	var sipStack;
	var registerSession;
	var initilized;
	var obj;

	var do_login = function() {
		if (!initilized) {
			alert("Can not initilize WebRTC");
			return;
		}
		if (registerSession != null) {
			log("Already Registered");
			return;
		}
		log("WebRTC initialized");
		createSipStack(); // see next section
		sipStack.start();
	}

	var do_accept = function() {
		obj.newSession.accept({
			events_listener: {
				events: '*',
				listener: eventsListener
			}
		});
		log('In Call');
		document.getElementById("btn_accept").style.display = "none";
	}

	var do_call = function() {
		oSipSessionCall = sipStack.newSession('call-audio');
		// make call
		var number = document.getElementById('number').value
		if (oSipSessionCall.call(number) != 0) {
			oSipSessionCall = null;
			alert("failed!");
		}
	}


	var do_logout = function() {
		if (registerSession == null) {
			log("No registered sip");
			return;
		}
		registerSession.unregister();
	}

	var login = function() {

		registerSession = sipStack.newSession('register', {
			events_listener: {
				events: '*',
				listener: eventsListener
			} // optional: '*' means all events
		});
		registerSession.register();
	}

	var log = function(msg) {
		document.getElementById("content").innerHTML = msg;
	}

	var eventsListener = function(e) {
		console.info('session event = ' + e.type);
		switch (e.type) {
			case "connected":
				if (e.session == registerSession) {
					log("SIP Registered.");
				}
				break;
			case "terminated":
				if (e.session == registerSession) {
					obj = null;
					registerSession = null;

					log("SIP Unregistered");
				} else {
					log(e.description);
				}
				break;
			case "started":
				login();
				break;
			case "i_new_message":
				log("Incoming Message...");
				break;
			case "i_new_call":
				log("Incoming Call");
				document.getElementById("btn_accept").style.display = "block";
				obj = e;
				break;
		}

	}

	var readyCallback = function(e) {
		initilized = true;

	}

	var errorCallback = function(e) {
		initilized = false;
		console.error('Failed to initialize the engine: ' + e.message);
	}
	SIPml.init(readyCallback, errorCallback);

	function createSipStack() {
		sipStack = new SIPml.Stack({
			realm: 'asterisk.org', // mandatory: domain name
			impi: document.getElementById('sip').value, // mandatory: authorization name (IMS Private Identity)
			impu: document.getElementById('public_identity').value, // mandatory: valid SIP Uri (IMS Public Identity)
			password: document.getElementById('password').value, // optional
			display_name: document.getElementById('display_name').value, // optional
			websocket_proxy_url: 'wss://192.168.2.1:8089/ws', // optional
			outbound_proxy_url: 'udp://192.168.2.1:5060', // optional
			enable_rtcweb_breaker: false, // optional


			events_listener: {
				events: '*',
				listener: eventsListener
			}, // optional: '*' means all events
			sip_headers: [ // optional
				{
					name: 'User-Agent',
					value: 'TelC WebRTC'
				},
				{
					name: 'Organization',
					value: 'TelC'
				}
			]
		});
	}


</script>