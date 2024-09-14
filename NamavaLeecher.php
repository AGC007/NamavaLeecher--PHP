<?php

#~~~~~~~ Var Set ~~~~~~~#
define('Account' ,'--- Namava Account ---');
#~~~~~~~ Var Set ~~~~~~~#

#-------- Get Page ID --------#

if (isset($_REQUEST['page_url']))// Get Page ID
{
    $Link_SPL = preg_split("/\//", $_GET['page_url']);
    $MovieKey = explode('-' , $Link_SPL[4])[0];

    if(strstr($_REQUEST['page_url'] , "series"))
    {
        $MovieType = "series";
        NamavaLeecher(Account , $MovieKey , $MovieType);
    } else{
        $MovieType = "movie";
        NamavaLeecher(Account , $MovieKey , $MovieType);
    }
}

#---------- Namava Leecher ----------#

function NamavaLeecher($Account , $MovieKey , $MovieType)// Leech Movie
{
    #~~~~~~~ Var Set ~~~~~~~#
    $NamavaCookie = file_get_contents('cookie.txt');
    #~~~~~~~ Var Set ~~~~~~~#

    #``````````` HEADER REQ ```````````#
    $HEADER_REQ = array(
        'cookie: '.$NamavaCookie,
        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36 Edg/127.0.0.0',
    );
    #``````````` HEADER REQ ```````````#

    if($MovieType == 'series')
    {
        #--------------- GET Series Data ---------------#
        $REQ_GET_SERIES = curl_init();

        curl_setopt($REQ_GET_SERIES, CURLOPT_URL, "https://www.namava.ir/api/v1.0/medias/{$MovieKey}/series-preview");
        curl_setopt($REQ_GET_SERIES, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($REQ_GET_SERIES, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($REQ_GET_SERIES, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($REQ_GET_SERIES, CURLOPT_HTTPHEADER, $HEADER_REQ);
        curl_setopt($REQ_GET_SERIES, CURLOPT_TIMEOUT, 30);
        #--------------- GET Series Data ---------------#

        $SERIES_DATA_RES = curl_exec($REQ_GET_SERIES);
        $SERIES_DATA_RES_JS = json_decode($SERIES_DATA_RES , true);
        curl_close($REQ_GET_SERIES);

        #---- Series Data ----#
        $SeriesSeasonsCount = Count($SERIES_DATA_RES_JS['result']['seasons']);
        $SeriesTitle = $SERIES_DATA_RES_JS['result']['seriesCaption'];

        for($A=0; $A <= $SeriesSeasonsCount-1; $A++)// Go To Seasons
        {
            $SeriesSeasonsID[$A] = $SERIES_DATA_RES_JS['result']['seasons'][$A]['seasonId'];
            $SeriesSeasonsName[$A] = $SERIES_DATA_RES_JS['result']['seasons'][$A]['seasonName'];
            $SeriesSeasonsCaption[$A] = $SERIES_DATA_RES_JS['result']['seasons'][$A]['seasonCaption'];
            $SeriesSeasonsOrderId[$A] = $SERIES_DATA_RES_JS['result']['seasons'][$A]['seasonOrderId'];
            $SeriesEpisodesCount = Count($SERIES_DATA_RES_JS['result']['seasons'][$A]['episodes']);

            for($B=0; $B <= $SeriesEpisodesCount-1; $B++)// Go To Seasons
            {
                $SeriesEpisodesID[$A][$B] =  $SERIES_DATA_RES_JS['result']['seasons'][$A]['episodes'][$B]['episodeId'];
                $SeriesEpisodesCaption[$A][$B] = $SERIES_DATA_RES_JS['result']['seasons'][$A]['episodes'][$B]['episodeCaption'];
            }
        }
        #---- Series Data ----#

        #~~~~ Series Json ~~~~#
        echo(json_encode(array(
            'code' => http_response_code(),
            'message' => 'success' ,
            'developer' => 'AGC007',
            'data' =>   array(
                'SeriesName' => $SeriesTitle ,
                'SeriesSeasonsCount' => $SeriesSeasonsCount ,
                'SeriesEpisodesCount' => $SeriesEpisodesCount ,
                'Seasons' => array(
                    'SeriesSeasonsID' => $SeriesSeasonsID,
                    'SeriesSeasonsName' => $SeriesSeasonsName,
                    'SeriesSeasonsCaption' => $SeriesSeasonsCaption,
                    'Episodes' => array(
                        'SeriesEpisodesID' =>  $SeriesEpisodesID,
                        'SeriesEpisodesCaption' =>  $SeriesEpisodesCaption,
                    ))))));
        #~~~~ Series Json ~~~~#
    } else {

        #--------------- GET Movie Data ---------------#
        $REQ_GET_MOVIE = curl_init();

        curl_setopt($REQ_GET_MOVIE, CURLOPT_URL, "https://www.namava.ir/api/v1.0/medias/{$MovieKey}/play?isKid=false");
        curl_setopt($REQ_GET_MOVIE, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($REQ_GET_MOVIE, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($REQ_GET_MOVIE, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($REQ_GET_MOVIE, CURLOPT_HTTPHEADER, $HEADER_REQ);
        curl_setopt($REQ_GET_MOVIE, CURLOPT_TIMEOUT, 30);
        #--------------- GET Movie Data ---------------#

        $MOVIE_DATA_RES = curl_exec($REQ_GET_MOVIE);
        $MOVIE_DATA_RES_JS = json_decode($MOVIE_DATA_RES , true);
        curl_close($REQ_GET_MOVIE);

        if($MOVIE_DATA_RES_JS['result']['relativePath'] != null)
        {
            #---- Movie Data ----#
            $MovieTitle = $MOVIE_DATA_RES_JS['result']['media']['caption'];
            $MovieImage = "https://static.namava.ir".$MOVIE_DATA_RES_JS['result']['media']['imageUrl'];
            $MovieTime = $MOVIE_DATA_RES_JS['result']['media']['movieEndTime'];
            $MovieDuration = $MOVIE_DATA_RES_JS['result']['media']['mediaDuration'];
            $MovieCategories = $MOVIE_DATA_RES_JS['result']['media']['categories'][0]['categoryName'];
            $MovieM3u8 = $MOVIE_DATA_RES_JS['result']['absolutePath'];
            #---- Movie Data ----#

            #--------------- GET DL Data ---------------#
            $REQ_GET_DL = curl_init();

            curl_setopt($REQ_GET_DL, CURLOPT_URL, "https://www.namava.ir/api/v1.0/medias/{$MovieKey}/download-info");
            curl_setopt($REQ_GET_DL, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($REQ_GET_DL, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($REQ_GET_DL, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($REQ_GET_DL, CURLOPT_HTTPHEADER, $HEADER_REQ);
            curl_setopt($REQ_GET_DL, CURLOPT_TIMEOUT, 30);
            #--------------- GET DL Data ---------------#

            $MOVIE_DL_RES = curl_exec($REQ_GET_DL);
            $MOVIE_DL_RES_JS = json_decode($MOVIE_DL_RES , true);
            curl_close($REQ_GET_DL);

            #---- Movie DL Data ----#
            $list_count = count($MOVIE_DL_RES_JS['result']['videos']);

            for($i=0; $i <= $list_count-1; $i++) {
                $MovieMainFullAbsolutePath[$i] = $MOVIE_DL_RES_JS['result']['videos'][$i]['absolutePath'];
                $MovieMainWidth[$i] = $MOVIE_DL_RES_JS['result']['videos'][$i]['width'];
                $MovieMainHeight[$i] = $MOVIE_DL_RES_JS['result']['videos'][$i]['height'];
                $MovieMainEncryptionKey = $MOVIE_DL_RES_JS['result']['encryption']['encryptionKey'];
                $MovieMainEncryptionIV = $MOVIE_DL_RES_JS['result']['encryption']['encryptionIV'];
                $MovieMainTracks = $MOVIE_DL_RES_JS['result']['tracks'][1]['absolutePath'];
                $MovieMainTracksTitle = rawurlencode($MovieTitle);

                $MovieMainBaseUrl[$i] = explode('ev', $MovieMainFullAbsolutePath[$i])[0];
                $MovieMainQueryParamX[$i] = explode('=', $MovieMainFullAbsolutePath[$i])[1];

                $MainData[$i] = array(
                    "audios" => array(),
                    "avBaseUrl" => $MovieMainBaseUrl[$i],
                    "avQueryParamX" => $MovieMainQueryParamX[$i],
                    "duration" => $MovieDuration,
                    "encryption" => array(
                        $MovieMainEncryptionKey,
                        $MovieMainEncryptionIV
                    ),
                    "imageUrl" => $MovieImage,
                    "mediaId" => intval($MovieKey),
                    "subtitles" => array(),
                    "thumbnailsUrl" => $MovieMainTracks,
                    "title" => $MovieMainTracksTitle,
                    "billingAccess" => array(
                        "hasBillingAccess" => true,
                        "aclDownloadable" => true,
                        "loginRequired" => true
                    ),
                    "width" => $MovieMainWidth[$i],
                    "height" => $MovieMainHeight[$i],
                    "videoUrl" => "ev_{$MovieMainWidth[$i]}.mp4",
                    "externalUrl" => "https://www.namava.ir/download/212701/{$MovieMainHeight[$i]}p"
                );

                $MainData_json_encoded[$i] = json_encode($MainData[$i], JSON_PRETTY_PRINT);
                $MainData_Offline_link[$i] = "nmvopdl://" . base64_encode($MainData_json_encoded[$i]);
            }
            #---- Movie DL Data ----#

            #~~~~ Movie Json ~~~~#
            echo(json_encode(array(
                'code' => http_response_code(),
                'message' => 'success' ,
                'developer' => 'AGC007',
                'data' =>   array(
                    'MovieName' => $MovieTitle ,
                    'MovieGenre' => $MovieCategories ,
                    'MovieTime' => $MovieTime ,
                    'MoviePoster' => $MovieImage ,
                    'dl' => array(
                        'M3u8_DownloadLink' => $MovieM3u8,
                        'Quality_list' => $MovieMainHeight ,
                        'Offline_DownloadLink_list' => $MainData_Offline_link ,
                        'Developer' => "AGC007"
                    )))));

            #~~~~ Movie Json ~~~~#

        }
        else if ($MOVIE_DATA_RES_JS['result']['relativePath'] == null )
        {
            //NamavaLeecherLogin($Account , $MovieKey , $MovieType);
        }
        else {
            echo(json_encode(array(
                'code' => '503',
                'message' => 'Process Error' ,
                'developer' => 'AGC007',
            )));
        }
    }
}
function NamavaLeecherLogin($Account , $MovieKey , $MovieType)// Login
{
    $exp = explode(":" , $Account);
    $Username = "+98".substr($exp[0] , 1);
    $Password = $exp[1];

    #``````````` HEADER LOGIN ```````````#
    $HEADER_LOGIN = array(
        'accept: application/json, text/plain, */*',
        'accept-encoding: gzip, deflate, br, zstd',
        'accept-language: en-US,en;q=0.9',
        'content-type: application/json;charset=UTF-8',
        'origin: https://www.namava.ir',
        'referer: https://www.namava.ir/auth/login',
        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0',
        'x-application-type: WebClient',
        'x-client-version: 2.69.1 ',
    );
    #``````````` HEADER LOGIN ```````````#

    #--------------- POST Request LOGIN ---------------#
    $REQ_LOGIN = curl_init();

    curl_setopt($REQ_LOGIN, CURLOPT_URL, "https://www.namava.ir/api/v1.0/accounts/login");
    curl_setopt($REQ_LOGIN, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($REQ_LOGIN, CURLOPT_POSTFIELDS, "{\"UserName\":\"{$Username}\",\"Password\":\"{$Password}\"}");
    curl_setopt($REQ_LOGIN, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($REQ_LOGIN, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($REQ_LOGIN, CURLINFO_HEADER_OUT, true);
    curl_setopt($REQ_LOGIN, CURLOPT_HEADER, true);
    curl_setopt($REQ_LOGIN, CURLOPT_HTTPHEADER, $HEADER_LOGIN);
    curl_setopt($REQ_LOGIN, CURLOPT_TIMEOUT, 30);
    #--------------- POST Request LOGIN ---------------#

    $LOGIN_RES = curl_exec($REQ_LOGIN);
    curl_close($REQ_LOGIN);

    if(strstr($LOGIN_RES , "succeeded\":true"))
    {
        $cookie_parts = explode("set-cookie:", $LOGIN_RES);
        $a = explode("auth_v2", $cookie_parts[0])[1];
        $Auth_v2 =  "auth_v2".explode(";", $a)[0].";";
        // $Auth_v2 = trim(explode(";", $cookie_parts[0]));

        #``````````` HEADER USER ```````````#
        $HEADER_GET_INFO = array(
            'cookie: '.$Auth_v2,
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36 Edg/127.0.0.0',
        );
        #``````````` HEADER USER ```````````#

        #--------------- GET Account INFO ---------------#
        $REQ_GET_INFO = curl_init();

        curl_setopt($REQ_GET_INFO, CURLOPT_URL, "https://www.namava.ir/api/v1.0/users/info");
        curl_setopt($REQ_GET_INFO, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($REQ_GET_INFO, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($REQ_GET_INFO, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($REQ_GET_INFO, CURLOPT_HTTPHEADER, $HEADER_GET_INFO);
        curl_setopt($REQ_GET_INFO, CURLOPT_TIMEOUT, 30);
        #--------------- GET Account INFO ---------------#

        $GET_INFO_RES = curl_exec($REQ_GET_INFO);
        $GET_INFO_RES_JS = json_decode($GET_INFO_RES , true);
        curl_close($REQ_GET_INFO);

        if($GET_INFO_RES_JS['result']['subscription']['paymentMethod'] != "None")
        {
            #----- Save Cookie ----#
            file_put_contents('cookie.txt' , $Auth_v2);
            #----- Save Cookie ----#

            NamavaLeecher($Account , $MovieKey ,$MovieType);

        } else {
            echo(json_encode(array(
                'code' => '403',
                'message' => 'Account Subscription Error' ,
                'developer' => 'AGC007',
            )));
        }
    } else {
        echo(json_encode(array(
            'code' => '404',
            'message' => 'Account Login Error' ,
            'developer' => 'AGC007',
        )));
    }
}

#---------- Namava Leecher ----------#

#~~ Developer : AGC007

?>







