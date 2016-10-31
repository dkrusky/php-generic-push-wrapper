<?php

// APP Specific Settings
define('PUSH_ALERT_TITLE',					'');
define('PUSH_ALERT_STRING',					'');
define('PUSH_ALERT_SOUND',					'');

// Blackberry
define('PUSH_BLACKBERRY_APPID',				'');
define('PUSH_BLACKBERRY_PASSWORD',			'');

// Apple iOS
define('PUSH_APNS_CERTIFICATE',				'');
define('PUSH_APNS_CERTIFICATE_PASSPHRASE',	'');

// Android
define('PUSH_GCM_KEY',						'');
define('PUSH_FCM_KEY',						'');
define('PUSH_FCM_CLICK_ACTION',				'');


class PushWrapper {
	
	public static function push($type, $deviceid, $options) {

		$response = Array(
			'success' => false,
			'deviceid' => $deviceid,
			'type' => $type,
		);
	
		switch($type) {
			case 0: // android
				$headers = Array();
				$payload = '';
				$server = '';

				if(PUSH_GCM_KEY != '') {
					$headers = array(
						'Authorization: key=' . PUSH_GCM_KEY,
						'Content-Type: application/json'
					);
					
					// Google Cloud Messaging Endpoint
					$server = 'https://android.googleapis.com/gcm/send';
					
					$gcm = Array('registration_ids' => Array($deviceid));
					if(!empty($options)) {
						if(isset($options['custom'])) {
							$gcm['data'] = $options['custom'];
						}
						
						if(isset($options['alert'])) {
							$gcm['data']['message'] = $options['alert'];
						} elseif(isset($options['body'])) {
							$gcm['data']['message'] = $options['body'];
						}
						
						if(isset($options['title'])) {
							$gcm['data']['title'] = $options['title'];
						}
						
						if(isset($options['sound'])) {
							$gcm['data']['sound'] = $options['sound'];
						}
						
						if(isset($options['vibrate'])) {
							$gcm['vibrate'] = $options['vibrate'];
						}
					}
					
					// default message
					if(!isset($gcm['data']['message'])) {
						$gcm['data']['message'] = PUSH_ALERT_STRING;
					}

					// default title
					if(!isset($gcm['data']['title'])) {
						$gcm['data']['alert'] = PUSH_ALERT_STRING;
					}

					$payload = $gcm;
					
				} elseif (PUSH_FCM_KEY != '') {
					$headers = array(
						'Authorization: key=' . PUSH_GCM_KEY,
						'Content-Type: application/json'
					);

					// Firebase Notifications Endpoint
					$server = 'https://fcm.googleapis.com/fcm/send';

					$fcm = Array('to' => $deviceid);
					if(!empty($options)) {
						if(isset($options['custom'])) {
							$fcm['data'] = $options['custom'];
						}
						
						if(isset($options['alert'])) {
							$fcm['data']['message'] = $options['alert'];
							$fcm['notification']['text'] = $options['alert'];
						} elseif(isset($options['body'])) {
							$fcm['data']['message'] = $options['body'];
							$fcm['notification']['text'] = $options['body'];
						}
						
						if(isset($options['title'])) {
							$fcm['data']['title'] = $options['title'];
							$fcm['notification']['title'] = $options['title'];
						}
						
						if(isset($options['sound'])) {
							$fcm['notification']['sound'] = $options['sound'];
						}
						
					}
					
					// default message
					if(!isset($fcm['data']['message'])) {
						$fcm['data']['message'] = PUSH_ALERT_STRING;
					}
					if(!isset($fcm['notification']['text'])) {
						$fcm['notification']['text'] = PUSH_ALERT_STRING;
					}

					// default title
					if(!isset($fcm['data']['title'])) {
						$fcm['data']['title'] = PUSH_ALERT_TITLE;
					}
					if(!isset($fcm['notification']['title'])) {
						$fcm['data']['title'] = PUSH_ALERT_TITLE;
					}

					// set click_action
					if(PUSH_FCM_CLICK_ACTION != '') {
						$fcm['notification']['click_action'] = PUSH_FCM_CLICK_ACTION;
					}
					
					$payload = $fcm;

				}
				
				if($server != '') {
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $server);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

					// curl_setopt($ch, CURL_IPRESOLVE_V4, true);
					curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); 

					// Disabling SSL Certificate support temporarly
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

					// Execute post
					$result = curl_exec($ch);
					
					if ($result === false) {
						$response['error'] = 'Curl failed: ' . curl_error($ch);
					} else {
						$response['success'] = true;
						$response['details'] = $result;
					}

					curl_close($ch);
				} else {
					$response['error'] = 'Missing value for PUSH_FCM_KEY or PUSH_GCM_KEY';
				}			
				break;

			case 1: // ios
			
				if(PUSH_APNS_CERTIFICATE == '') {
					$response['error'] = 'Missing value for PUSH_APNS_CERTIFICATE';
				} else {
					if(!file_exists(PUSH_APNS_CERTIFICATE)) {
						$response['error'] = 'The file \'' . PUSH_APNS_CERTIFICATE . '\' does not exist or is inaccessible due to safe mode restrictions';
					} else {
						// Create stream context
						$stream_context = stream_context_create();
						stream_context_set_option($stream_context, 'ssl', 'local_cert', PUSH_APNS_CERTIFICATE);
						stream_context_set_option($stream_context, 'ssl', 'passphrase', PUSH_APNS_CERTIFICATE_PASSPHRASE);
						$stream = stream_socket_client(PUSH_APNS_SERVER, $error, $error_string, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $stream_context);

						// Check if stream was created
						if (!$stream) {
							$response['success'] = false;
							$response['error'] = $error . ' ' . $error_string;
						} else {
							
							// Create the payload body
							$apns = Array( 'aps'	=>	Array() );
							
							if(!empty($options)) {
								if(isset($options['custom'])) {
									$apns['msg'] = $options['custom'];
								}
								
								if(isset($options['badge'])) {
									$apns['apns']['badge'] = $options['badge'];
								}
								
								if(isset($options['alert'])) {
									$apns['apns']['alert'] = $options['alert'];
								}
								
								if(isset($options['title'])) {
									$apns['apns']['title'] = $options['title'];
								}
								
								if(isset($options['sound'])) {
									$apns['apns']['sound'] = $options['sound'];
								}
								
								if(isset($options['body'])) {
									$apns['apns']['body'] = $options['body'];
								}
							}
							
							if(!isset($apns['apns']['alert'])) {
								$apns['apns']['alert'] = PUSH_ALERT_STRING;
							}

							$response['payload'] = $apns;
							$json = json_encode($apns);

							// Build the binary notification
							$payload = chr(0) . pack('n', 32) . pack('H*', str_replace(' ', '', $deviceid)) . pack('n', strlen($json)) . $json;

							// Send it to the server
							$result = fwrite($stream, $payload, strlen($payload));

							if (!$result) {
								$response['error'] = 'Message not delivered';
							} else {
								$response['success'] = true;
								$response['details'] = 'APNS Result not implemented yet. This is not an error.';
								/* TODO: Recieve Apple Error Data
								$apple_error = "";
								$responseBinary = fread($fp, 6);
								if ($responseBinary != false || strlen($responseBinary) == 6) {
									//convert it from it's binary stream state and print. 
									$apple_error = unpack('Ccommand/Cstatus_code/Nidentifier', $responseBinary);
								}

								// echo 'Message successfully delivered amar'.$message. PHP_EOL;
								$debug_msg = "Packet Delivered to APNS of " . var_export($result, true) . " bytes.";
								if($apple_error <> "") {
									$debug_msg .= '<br />Error: ' . var_export($apple_error, true);
								} else {
									$debug_msg .= '<br />No Errors from Apple';
								}
								
								/* OR 
								// $response = stream_get_contents($stream);
								// echo $response . '<hr />';
								*/
							}
							
						}
						
						// Close the connection to the server
						fclose($stream);
					}
				}
				break;
			case 2: // blackberry

				if(PUSH_BLACKBERRY_APPID == '' || PUSH_BLACKBERRY_PASSWORD == '') {
					$response['error'] = 'Missing values for PUSH_BLACKBERRY_APPID or PUSH_BLACKBERRY_PASSWORD';
				} else {
					//Deliver before timestamp
					$deliverbefore = gmdate('Y-m-d\TH:i:s\Z', strtotime('+5 minutes'));
					
					$message = '';
					if(isset($options['alert'])) {
						$message = $options['alert'];
					} elseif (isset($options['body'])) {
						$message = $options['body'];
					} else {
						$message = PUSH_ALERT_STRING;
					}
					
					// Unique message id
					$id = microtime(true);

					$payload = "--mPsbVQo0a68eIL3OAxnm\r\nContent-Type: application/xml; charset=UTF-8\r\n\r\n<?xml version=\"1.0\"?>\r\n\<!DOCTYPE pap PUBLIC \"-//WAPFORUM//DTD PAP 2.1//EN\" \"http://www.openmobilealliance.org/tech/DTD/pap_2.1.dtd\">\r\n<pap>\r\n<push-message push-id=\"$id\" deliver-before-timestamp=\"$deliverbefore\" source-reference=\"" . PUSH_BLACKBERRY_APPID . "\">\r\n\r\n<address address-value=\"$deviceid\"/>\r\n<quality-of-service delivery-method=\"unconfirmed\"/>\r\n</push-message>\r\n</pap>\r\n--mPsbVQo0a68eIL3OAxnm\r\nContent-Type: text/plain\r\nPush-Message-ID: $id\r\n\r\n$message\r\n--mPsbVQo0a68eIL3OAxnm--\r\n";

					$response['payload'] = $payload;

					// set headers
					$headers = array(
						"Content-Type: multipart/related; boundary=mPsbVQo0a68eIL3OAxnm; type=application/xml",
						"Accept: text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2",
						"Connection: keep-alive")
					);

					$ch = curl_init();
					// set URL and other appropriate options
					curl_setopt($ch, CURLOPT_URL, "https://pushapi.eval.blackberry.com/mss/PD_pushRequest");
					curl_setopt($ch, CURLOPT_HEADER, false);
					curl_setopt($ch, CURLOPT_USERAGENT, "Hallgren Networks BB Push Server/1.0");
					curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
					curl_setopt($ch, CURLOPT_USERPWD, PUSH_BLACKBERRY_APPID . ':' . PUSH_BLACKBERRY_PASSWORD);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					// curl_setopt($ch, CURL_IPRESOLVE_V4, true);
					curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); 

					// Disabling SSL Certificate support temporarly
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					
					$result = curl_exec($ch);

					//Start parsing response into XML data that we can read and output
					if ($result === false) {
						$response['error'] = 'Curl failed: ' . curl_error($ch);
					} else {
						try {
							$p = xml_parser_create();
							xml_parse_into_struct($p, $result, $vals);
							$errorcode = xml_get_error_code($p);
							if ($errorcode > 0) {
								$response['error'] = "Error " . $vals[1]['attributes']['CODE'] . " " . $vals[1]['attributes']['DESC'] . " [" . xml_error_string($errorcode) . "]";
							} else {
								if($vals[1]['tag'] == 'PUSH-RESPONSE') {
									$response['success'] = true;
									$response['details'] = Array(
										'PUSH-ID' => $vals[1]['attributes']['PUSH-ID'],
										'REPLY-TIME' => $vals[1]['attributes']['REPLY-TIME'],
										'CODE' => $vals[2]['attributes']['CODE'],
										'DESC' => $vals[2]['attributes']['DESC']
									);
								} else {
									$response['error'] = 'Invalid response received';
								}
							}
							xml_parser_free($p);
						} catch (Exception $e) {
							$response['error'] = 'Curl failed: ' . curl_error($ch));
						}
					}

					curl_close($ch);
				}
				break;
			case 3: // windows phone
				$response['error'] = 'Not implemented';
				break;
				
			case 4: // windows device
				$response['error'] = 'Not implemented';
				break;
			default:
				$response['error'] = 'Invalid device type specified';
				break;
		}
		
		return $response;
	}
  
}
