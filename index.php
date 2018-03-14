<?php

require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot\SignatureValidator as SignatureValidator;

// load config
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// initiate app
$configs =  [
	'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);

/* ROUTES */
$app->get('/', function ($request, $response) {
	return "Lanjutkan!";
});

$app->post('/', function ($request, $response)
{
	// get request body and line signature header
	$body 	   = file_get_contents('php://input');
	$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

	// log body and signature
	file_put_contents('php://stderr', 'Body: '.$body);

	// is LINE_SIGNATURE exists in request header?
	if (empty($signature)){
		return $response->withStatus(400, 'Signature not set');
	}

	// is this request comes from LINE?
	if($_ENV['PASS_SIGNATURE'] == false && ! SignatureValidator::validateSignature($body, $_ENV['CHANNEL_SECRET'], $signature)){
		return $response->withStatus(400, 'Invalid signature');
	}

	// init bot
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

	$data = json_decode($body, true);
	foreach ($data['events'] as $event)
	{
		if ($event['type'] == 'message')
		{
			if($event['message']['type'] == 'text')
			{
				if ($event['message']['text'] == '!geser'){
					
					$geserBwank = 'https://i.imgur.com/2kHsfd5.jpg?_ignored';
					$area = new \LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(0,0,800,350);
					$size = new \LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder(350,800);
					$act = new \LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder('https://line.me/R/ti/g/P6LLTS4mCv',$area);
					$img = new \LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder($geserBwank, 'Imagemap', $size, array($act));
					
					$result = $bot->replyMessage($event['replyToken'], $img);
					return $result->getHTTPStatus() . ' ' . $result->getRawBody();
				
				} elseif ( $event['message']['text'] == '.help'){
					
					$ret = 'availabe commands: ' ."\xA". '".yufid [query]",".xkcd", ".qotd", ."qotd [username_ig]"'."\xA". '(available ig-s: muslimorid, themuslimshow, rumayshocom, alhikmahjkt, indonesiatauhid)'."\xA".'.quran';
					
					$result = $bot->replyText($event['replyToken'], $ret);
					return $result->getHTTPStatus() . ' ' . $result->getRawBody();	
				
				} elseif (substr($event['message']['text'], 0, 7) == '.yufid '){
					
					$teks = $event['message']['text'];
					
					$cut = str_ireplace(" ","%20", substr($teks, 7));
					
					$ret = 'http://yufid.com/result/?search=' .$cut;
					
					$result = $bot->replyText($event['replyToken'], $ret);
					return $result->getHTTPStatus() . ' ' . $result->getRawBody();		
					
				} elseif ( $event['message']['text'] == '.xkcd'){
				
					$comic = json_decode(file_get_contents("https://xkcd.com/". rand(1, json_decode(file_get_contents("https://xkcd.com/info.0.json"),true)['num']) . "/info.0.json"),true)['img'];
					
					$ret =  new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($comic, $comic);
					
					$result = $bot->replyMessage($event['replyToken'], $ret);
					return $result->getHTTPStatus() . ' ' . $result->getRawBody();
				
				
				} elseif (substr($event['message']['text'], 0, 5) == '.qotd'){
					
					if (substr($event['message']['text'], 6) == ''){	
						$rand = rand(1,5);
						switch ($rand){
							case 1:
								$username = 'themuslimshow';
								break;
							case 2:
								$username = 'indonesiatauhid';
								break;
							case 3:
								$username = 'muslimorid';
								break;
							case 4:
								$username = 'alhikmahjkt';
								break;
							case 5:
								$username = 'rumayshocom';
								break;
						}
					} else{
						
						$list = array('themuslimshow', 'indonesiatauhid', 'muslimorid', 'muslimorid', 'alhikmahjkt', 'rumayshocom'); 
						
						if(in_array(substr($event['message']['text'], 6), $list)){
						
							$teks = $event['message']['text'];
							$username = substr($teks, 6);
						}
						
						else{
							$poster = 'mmd';
						}
												
					}
					
					$html = file_get_contents('https://www.instagram.com/' .$username .= '/');
				
					preg_match_all( '/"display_src":"(.*?)"/',$html, $matches ); 
				
					$poster = $matches[1][rand(0,15)];
					$text = '#QuoteOfTheDay';
					
					$res =  new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
					
					$res->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text))
					->add(new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($poster, $poster)); 
					
					$result = $bot->replyMessage($event['replyToken'], $res);
					return $result->getHTTPStatus() . ' ' . $result->getRawBody();
					
				
					
				} elseif (substr($event['message']['text'], 0, 6) == '.quran'){
					
					if(substr($event['message']['text'], 7, 4) == 'ayah'){
						
						$text = substr($event['message']['text'], 12); 
						$html = json_decode(file_get_contents( 'https://api.alquran.cloud/ayah/'.$text.='/editions/quran-uthmani,id.indonesian'),true);
						
						if(empty($html)){
							
							$text = 'Please enter the correct ayah number';
							$poster = 'https://i.imgur.com/b7dvCF1.jpg?_ignored';
						
							$res =  new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
					
							$res->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text))
							->add(new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($poster, $poster)); 
					
							$result = $bot->replyMessage($event['replyToken'], $res);
							return $result->getHTTPStatus() . ' ' . $result->getRawBody();
							
							}
						
						else{
							
							$translate =  $html['data'][0]['text']."\xA"."\xA".$html['data'][1]['text']."\xA"."[".$html['data'][0]['surah']['englishName'].':'.$html['data'][0]['surah']['number'].':'.$html['data'][0]['numberInSurah']."]";
						
							$res = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($translate);
							
							$result = $bot->replyMessage($event['replyToken'], $res);
							return $result->getHTTPStatus() . ' ' . $result->getRawBody();
							
							}
					} 
					
					elseif(substr($event['message']['text'], 7, 4)=='rand'){
						
						$rand = rand(1,6232);
						
						$html = json_decode(file_get_contents( 'https://api.alquran.cloud/ayah/'.$rand.='/editions/quran-uthmani,id.indonesian'),true);
						
						$translate =  $html['data'][0]['text']."\xA"."\xA".$html['data'][1]['text']."\xA"."[".$html['data'][0]['surah']['englishName'].':'.$html['data'][0]['surah']['number'].':'.$html['data'][0]['numberInSurah']."]";
						
						$res = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($translate);
							
						$result = $bot->replyMessage($event['replyToken'], $res);
						return $result->getHTTPStatus() . ' ' . $result->getRawBody();
						
					}
					
					elseif(substr($event['message']['text'], 7, 6) == 'search'){
	
						$que = substr($event['message']['text'],14);
						
						if(is_numeric($que[0])){
							
							$surah = substr($que, 0 , (strpos($que , " ")));
							$src = substr($que, strpos($que , " ")+1);
							
							$source = json_decode(file_get_contents('https://api.alquran.cloud/search/' .$src. '/' .$surah. '/id.indonesian'),true);
							
							}
						
						else{
							
							$source = json_decode(file_get_contents('https://api.alquran.cloud/search/' .substr($event['message']['text'], 14). '/all/id.indonesian'),true);
							
							}
						
						if(empty($source)){
							
							$res = "No result is found";
							
							}
						
						
						else{
							
							$res = "There are: ".$source['data']['count']." matches"."\xA"."\xA";
						
							for($i = 0; $i < 10; $i++){
							
								$ayah = substr($source['data']['matches'][$i]['text'], 0, 20);
								$ref = $source['data']['matches'][$i]['surah']['englishName'] .":". $source['data']['matches'][$i]['surah']['number'] .":". $source['data']['matches'][$i]['numberInSurah'];
								
								if(empty($ayah)){
									break;
									
									}
								$res .= $ayah ."..."."\xA"."[". $ref ."]" ."\xA"."\xA";
							
								}
							
							}
						
						$ress = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($res);
						
						$result = $bot->replyMessage($event['replyToken'], $ress);
				    	return $result->getHTTPStatus() . ' ' . $result->getRawBody();
					}
					
					else{
						
						$text = 'how to use:' ."\xA". '.quran ayah [surah]:[no.ayat]' ."\xA". 'ex: .quran ayah 1:6' ."\xA"."\xA". '.quran search [query]' ."\xA". 'ex:.quran search taubat' ."\xA". '.quran search [surah number] [query]' ."\xA". 'ex: .quran search 2 taubat' ."\xA"."\xA". '.quran rand';
						$poster = 'https://i.imgur.com/b7dvCF1.jpg?_ignored';
						
						$res =  new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
					
						$res->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text))
						->add(new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($poster, $poster)); 
					
						$result = $bot->replyMessage($event['replyToken'], $res);
						return $result->getHTTPStatus() . ' ' . $result->getRawBody();
						
					}
				}
			}
		}
	}

});

// $app->get('/push/{to}/{message}', function ($request, $response, $args)
// {
// 	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
// 	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

// 	$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($args['message']);
// 	$result = $bot->pushMessage($args['to'], $textMessageBuilder);

// 	return $result->getHTTPStatus() . ' ' . $result->getRawBody();
// });

/* JUST RUN IT */
$app->run();

//naro template disini
