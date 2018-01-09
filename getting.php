<?php

//Wrapper delle fonti #emergenzeprato e preparazione dati di interesse per i vari bot
//questa classe deve essere istanziata nei vari JOB che vogliono usare i dati
//by MT


class getdata {

  public function get_musei($lat,$lon)
{
  $alert="";
  $reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1";
  $json_string = file_get_contents($reply);
  $parsed_json = json_decode($json_string);
  //var_dump($parsed_json);
  $comune="";
  $temp_c1 =$parsed_json->{'display_name'};

  if ($parsed_json->{'address'}->{'town'}) {
    $temp_c1 .="\nCittà: ".$parsed_json->{'address'}->{'city'};

  }
    $comune .=$parsed_json->{'address'}->{'town'};

    //echo $comune;

  	$html = file_get_contents('http://151.12.58.144:8080/DBUnicoManagerWeb/dbunicomanager/searchPlace?tipologiaLuogo=1&comune='.$comune);
    //echo $html;
  	//$html = iconv('ASCII', 'UTF-8//IGNORE', $html);
    $html=utf8_decode($html);
  $html=str_replace("<![CDATA[","",$html);
  $html=str_replace("]]>","",$html);

  	$doc = new DOMDocument;
  	$doc->loadHTML($html);

  	$xpa    = new DOMXPath($doc);
    //var_dump($doc);
    $divs0   = $xpa->query('//luogodellacultura');

  	$divs   = $xpa->query('//luogodellacultura/proprieta');
    $divs1   = $xpa->query('//luogodellacultura/denominazione');
    $divs2   = $xpa->query('//luogodellacultura/descrizione');
    $divs3   = $xpa->query('//luogodellacultura/traduzione');
    $divs4   = $xpa->query('//luogodellacultura/orario/testostandard');
    $divs5   = $xpa->query('//info/sitoweb');
    $divs6   = $xpa->query('//info/email');
    $divs7   = $xpa->query('//info/telefono/testostandard');
    $divs8   = $xpa->query('//info/chiusuraSettimanale/testostandard');
    $divs9   = $xpa->query('//biglietteria/orario-biglietteria/testostandard');

  $diva=[];
  $diva1=[];
  $diva2=[];
  $diva3=[];
  $diva4=[];
  $diva5=[];
  $diva6=[];
  $diva7=[];
  $diva8=[];
  $count=0;
  	foreach($divs as $div) {
        array_push($diva,$div->nodeValue);
  $count++;
  	}

    foreach($divs1 as $div1) {

          array_push($diva1,$div1->nodeValue);
    }

    foreach($divs2 as $div2) {

          array_push($diva2,$div2->nodeValue);
    }
    foreach($divs3 as $div3) {
        $allerta3 .= "\n<br>".$div3->nodeValue;
          array_push($diva3,$div3->nodeValue);
    }
    foreach($divs4 as $div4) {

          array_push($diva4,$div4->nodeValue);
    }
    foreach($divs5 as $div5) {

          array_push($diva5,$div5->nodeValue);
    }
    foreach($divs6 as $div6) {

          array_push($diva6,$div6->nodeValue);
    }
    foreach($divs7 as $div7) {

          array_push($diva7,$div7->nodeValue);
    }
    foreach($divs8 as $div8) {

          array_push($diva8,$div8->nodeValue);
    }

  for ($i=0;$i<$count;$i++){

  $alert.= "Ente: ".$diva1[$i];
  $alert.= "\n</br>Proprietà: ".$diva[$i];
  $alert.= "\n</br>Descrizione: ".$diva2[$i];
  if ($diva3[$i]!=NULL) $alert.= "\n</br> ".$diva3[$i];
  if ($diva4[$i]!=NULL) $alert.= "\n</br>Apertura: ".$diva4[$i];
  if ($diva5[$i]!=NULL)$alert.= "\n</br>Sitoweb: ".$diva5[$i];
  if ($diva6[$i]!=NULL) $alert.= "\n</br>Email: ".$diva6[$i];
  if ($diva7[$i]!=NULL)$alert.= "\n</br>Telefono: ".$diva7[$i];
  if ($diva8[$i]!=NULL)$alert.= "\n</br>Chiusura settimanale: ".$diva8[$i];

  $alert .= "\n\n</br></br>";
  //$alert .= $ente.$proprieta.$descrizione.$descrizionetrad.$apertura.$sitoweb.$email.$telefono.$chiusura."\n</br>";

  }

return $alert;

}


public function get_revgeo($lat,$lon)
$reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lng."&zoom=18&addressdetails=1";
$json_string = file_get_contents($reply);
$parsed_json = json_decode($json_string);
//var_dump($parsed_json);
$temp_c1 =$parsed_json->{'display_name'};
if ($parsed_json->{'address'}->{'city'}) {
  $temp_c1 .="\nCittà: ".$parsed_json->{'address'}->{'city'};

}

  return $temp_c1;

}
  //rispondo
 public function get_fermateba($lat,$lon,$r)
 {



     $json_string = file_get_contents("http://bari.opendata.planetek.it/OrariBus/v2.1/OpenDataService.svc/REST/rete/FermateVicine/".$lat."/".$lon."/".$r);
     $parsed_json = json_decode($json_string);
     $count = 0;
     $countl = [];
     foreach($parsed_json as $data=>$csv1){
        $count = $count+1;
     }

  //   echo "Fermate più vicine rispetto a ".$lat."/".$lon." in raggio di ".$r." metri con relative linee urbane ed orari arrivi\n";

   $IdFermata="";
   //  echo $count;
 for ($i=0;$i<$count;$i++){
   foreach($parsed_json[$i]->{'ListaLinee'} as $data=>$csv1){
      $countl[$i] = $countl[$i]+1;
     }
   //echo $countl;
     $temp_c1 .="Fermata: ".$parsed_json[$i]->{'DescrizioneFermata'}."\nId Fermata: ".$parsed_json[$i]->{'IdFermata'};
     $temp_c1 .="\nVisualizzala su :\nhttp://www.openstreetmap.org/?mlat=".$parsed_json[$i]->{'PosizioneFermata'}->{'Latitudine'}."&mlon=".$parsed_json[$i]->{'PosizioneFermata'}->{'Longitudine'}."#map=19/".$parsed_json[$i]->{'PosizioneFermata'}->{'Latitudine'}."/".$parsed_json[$i]->{'PosizioneFermata'}->{'Longitudine'};
     $temp_c1 .="\nLinee servite :";
     for ($l=0;$l<$countl[$i];$l++)
       {


     $temp_c1 .="\nLinee: ".$parsed_json[$i]->{'ListaLinee'}[$l]->{'IdLinea'}." ".$parsed_json[$i]->{'ListaLinee'}[$l]->{'Direzione'};
        }
     $temp_c1 .="";


     // inzio sotto routine per orari per linee afferenti alla fermata:

     $IdFermata=$parsed_json[$i]->{'IdFermata'};
 //    echo $IdFermata;
     $json_string1 = file_get_contents("http://bari.opendata.planetek.it/OrariBus/v2.1/OpenDataService.svc/REST/OrariPalina/".$IdFermata."/");
     $parsed_json1 = json_decode($json_string1);
   //  var_dump($parsed_json1);
   //  var_dump($parsed_json1->{'PrevisioniLinee'}[0]);
     $countf = 0 ;
     foreach($parsed_json1->{'PrevisioniLinee'} as $data123=>$csv113){
        $countf = $countf+1;
     }
 //    echo $countf;
     $h = "2";// Hour for time zone goes here e.g. +7 or -4, just remove the + or -
     $hm = $h * 60;
     $ms = $hm * 60;
     date_default_timezone_set('UTC');
     for ($f=0;$f<$countf;$f++){

       $time =$parsed_json1->{'PrevisioniLinee'}[$f]->{'OrarioArrivo'}; //registro nel DB anche il tempo unix
   //    echo "\ntimestamp:".$time."senza pulizia dati";
       $time =str_replace("/Date(","",$time);
       $time =str_replace("000+0200)/","",$time);
   //    $time =str_replace("T"," ",$time);
   //    $time =str_replace("Z"," ",$time);
       $time =str_replace(" ","",$time);
       $time =str_replace("\n","",$time);
       $timef=floatval($time);
       $timeff = time();
       $timec =gmdate('H:i:s d-m-Y', $timef+$ms);

     //  echo "\ntimestamp:".$timef."con pulizia dati";

   //    $date = date_create();
     //echo date_format($date, 'U = Y-m-d H:i:s') . "\n";

   //  date_timestamp_set($date, $time);
   //  $orario=date_format($date, 'U = Y-m-d H:i:s') . "\n";
       $temp_c1 .="\nLinea: ".$parsed_json1->{'PrevisioniLinee'}[$f]->{'IdLinea'}." arrivo: ".$timec."";
   //    $temp_c1 .=" ".$time;
      }
       $temp_c1 .="\n\n";


     // fine sub routine

 }

  return $temp_c1;

 }
 }

?>
