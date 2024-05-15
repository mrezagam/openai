<?php

$openai = new openai('sk-proj-I3G3mFtRU09W6DbRbE8OT3BlbkFJgFqwHbogyoBytKWJPLQV');

/* Text generation */
/*
$model = "gpt-4o"; // gpt-4o gpt-4-turbo gpt-3.5-turbo
$msg   = "چیا بلدی ؟";
$conversion = array(
    array("role" => "system", "content" => "You are a helpful assistant."),
    array("role" => "user", "content" => "سلام"),
    array("role" => "assistant", "content" => "سلام! چگونه به من کمک کنم؟"),
    array("role" => "user", "content" => $msg)
);
$res = $openai->TexTGeneraTion($conversion,$model);
print_r($res);
*/

/* Image generation */
/*
$prompT = "عنکبوت برنامه";
$res    = $openai->ImageGeneraTion($prompT);
print_r($res);
*/

/* Text to speech */
/*
$T   = "مرحبا طایر فرخ پی فرخنده پیام";
$res = $openai->TexTToSpeech($T);
print_r($res);
*/

/*  Speech to text */
/*
$audio   = "media/mp3/1715775981243724.mp3";
$res     = $openai->SpeechToText($audio);
print_r($res);
*/



class openai
{
    private $Token;

    public function __construct($Token)
    {
        $this->Token = $Token;
    }

    private function curl($url,$req)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->Token
        ));
        $res = curl_exec($ch);
    
        if(curl_errno($ch))
        {
            $error_message = curl_error($ch);
            curl_close($ch);
            return false;
        }
        else
        {curl_close($ch); return $res;}
    }

    public function TexTGeneraTion($conversion, $model = "gpt-4o")
    {
        $url = "https://api.openai.com/v1/images/generations";
        $req = array("model" => $model, "messages" => $conversion);
        $res = $this->curl($url,$req);

        if ($res !== false) 
        {
            $res = json_decode($res, true);
            if (isset($res['choices'][0]['message']['content']))
            { return array("was" => true, "response" => $res['choices'][0]['message']['content']); } 
            else
            { return array("was" => false, "error" => $res); }
        }
        else
        { return array("was" => false, "error" => "Curl request failed"); }
    }
    
    public function ImageGeneraTion($prompT, $n = 1, $size = "1024x1024", $model = "dall-e-3")
    {
        $url = "https://api.openai.com/v1/images/generations";
        $req = array(
            "model" => $model,
            "prompt" => $prompT,
            "n" => $n,
            "size" => $size
        );
        $res = $this->curl($url,$req);
    
        if ($res !== false)
        {
            $res = json_decode($res, true);
            if (isset($res['data'][0]['url']))
            {

                $image = file_get_contents($res['data'][0]['url']);
                if ($image !== false)
                {
                    $file  = "media/img/" . time() ."". rand(1,1000000) . ".png";
                    $res = file_put_contents($file,$image);
                    if ($res !== false)
                    { return array("was" => true, "response" => $file); }
                    else
                    { return array("was" => false, "error" => "Error in saving the image file!"); }
                }
                else
                { return array("was" => false, "error" => "Error in saving the image file!"); }
            }
            else
            { return array("was" => false, "error" => $res); }
        }
        else
        { return array("was" => false, "error" => "Curl request failed"); }
    }

    public function TexTToSpeech($T, $voice = "alloy", $model = "tts-1")
    {
        $url = "https://api.openai.com/v1/audio/speech";
        $req = array(
            "model" => $model,
            "input" => $T,
            "voice" => $voice
        );
        $res = $this->curl($url, $req);
    
        if ($res !== false)
        {
            $file = "media/mp3/".time() ."". rand(1,1000000).".mp3";
            $res = file_put_contents($file, $res);
            if ($res !== false)
            {return array("was" => true, "response" => $file);}
            else
            {return array("was" => false, "error" => "Error in saving the audio file!");}
        }
        else
        {return array("was" => false, "error" => "Curl request failed");}
    }

    public function SpeechToText($audio, $model = "whisper-1")
    {
        if (!file_exists($audio))
        {return array("was" => false, "error" => "Audio file does not exist!");}
    
        $audioContent = file_get_contents($audio);
        if ($audioContent === false)
        { return array("was" => false, "error" => "Error reading the content of the audio file!"); }
    
        $url = "https://api.openai.com/v1/audio/transcriptions";
        $headers = array(
            "Authorization: Bearer " . $this->Token,
            "Content-Type: multipart/form-data"
        );
    
        $req = array("model" => $model,"file" => new \CURLFile(realpath($audio)));
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($rescode == 200 && $response !== false)
        {return array("was" => true, "response" => $response['text']);}
        else
        {return array("was" => false, "error" => "Error converting speech to text!");}
    }
}


?>