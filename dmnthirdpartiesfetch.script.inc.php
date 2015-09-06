<?php

/*
    This file is part of Dash Ninja.
    https://github.com/elbereth/dashninja-ctl

    Dash Ninja is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Dash Ninja is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Foobar.  If not, see <http://www.gnu.org/licenses/>.

 */

DEFINE('DMN_VERSION','2.1.0');

xecho('dmnthirdpartiesfetch v'.DMN_VERSION."\n");

$tp = array();

xecho("Fetching from Kraken: ");
try {
  $kraken = new \Payward\KrakenAPI('','');
  $dataKraken = $kraken->QueryPublic('Ticker', array('pair' => 'XBTCZEUR'));
  if (is_array($dataKraken) && isset($dataKraken['error']) && (count($dataKraken['error']) == 0)
    && isset($dataKraken['result']) && is_array($dataKraken['result'])
    && isset($dataKraken['result']['XXBTZEUR']) && is_array($dataKraken['result']['XXBTZEUR'])
    && isset($dataKraken['result']['XXBTZEUR']['p']) && is_array($dataKraken['result']['XXBTZEUR']['p'])
    && isset($dataKraken['result']['XXBTZEUR']['p'][1]) ) {
    $tp["eurobtc"] = array("StatValue" => $dataKraken['result']['XXBTZEUR']['p'][1],
                           "LastUpdate" => time(),
                           "Source" => "kraken");
    echo "OK (".$dataKraken['result']['XXBTZEUR']['p'][1]." EUR/BTC)\n";
  }
}
catch (Exception $e) {
  // Error
}

xecho("Fetching from Cryptsy: ");
$res = file_get_contents('http://pubapi2.cryptsy.com/api.php?method=singlemarketdata&marketid=155');
if ($res !== false) {
  $res = json_decode($res,true);
//  var_dump($res);
  if (($res !== false) && is_array($res) && (count($res) == 2) && array_key_exists('return',$res)
   && is_array($res["return"]) && array_key_exists("markets",$res["return"])
   && is_array($res["return"]["markets"]) && array_key_exists("DRK",$res["return"]["markets"])
   && is_array($res["return"]["markets"]["DRK"]) && array_key_exists("lasttradeprice",$res["return"]["markets"]["DRK"])) {
    $tp["btcdrk"] = array("StatValue" => $res["return"]["markets"]["DRK"]["lasttradeprice"],
                          "LastUpdate" => time(),
                          "Source" => "cryptsy");
    echo "OK (".$res["return"]["markets"]["DRK"]["lasttradeprice"]." BTC/DASH)\n";
  }
  else {
    echo "Failed (JSON)\n";
  }
}
else {
  echo "Failed (GET)\n";
}

/*
xecho("Fetching from Bitstamp: ");
$res = file_get_contents('https://www.bitstamp.net/api/ticker/');
if ($res !== false) {
  $res = json_decode($res,true);
  if (($res !== false) && is_array($res) && array_key_exists('timestamp',$res) && array_key_exists('last',$res)) {
    $tbstamp = date('Y-m-d H:i:s',$res['timestamp']);
    $sql[] = sprintf("('usdbtc','".$mysqli->real_escape_string($res['last'])."','".$tbstamp."','bitstamp')");
    $tp["usdbtc"] = array("StatValue" => $res["last"],
                          "LastUpdate" => intval($res['timestamp']),
                          "Source" => "bitstamp");
    echo "OK (".$res['last']." / $tbstamp)\n";
  }
  else {
    echo "Failed (JSON)\n";
  }
}
else {
  echo "Failed (GET)\n";
}
*/

xecho("Fetching from BTC-e: ");
$res = file_get_contents('https://btc-e.com/api/2/btc_usd/ticker');
if ($res !== false) {
  $res = json_decode($res,true);
  if (($res !== false) && is_array($res) && array_key_exists('ticker',$res) && array_key_exists('last',$res['ticker']) && array_key_exists('updated',$res['ticker'])) {
    $tbstamp = date('Y-m-d H:i:s',$res['ticker']['updated']);
    $tp["usdbtc"] = array("StatValue" => $res['ticker']["last"],
                          "LastUpdate" => intval($res['ticker']['updated']),
                          "Source" => "btc-e");
    echo "OK (".$res['ticker']['last']." / $tbstamp)\n";
  }
  else {
    echo "Failed (JSON)\n";
  }
}
else {
  echo "Failed (GET)\n";
}

xecho("Fetching from CoinMarketCap: ");
$res = file_get_contents('http://coinmarketcap-nexuist.rhcloud.com/api/dash');
$resdone = 0;
if ($res !== false) {
  $res = json_decode($res,true);
  if (($res !== false) && is_array($res) && array_key_exists('symbol',$res) && ($res['symbol'] == 'dash') && array_key_exists('timestamp',$res)) {
    $tbstamp = date('Y-m-d H:i:s',$res['timestamp']);
    if (array_key_exists('position',$res)) {
      $tp["marketcappos"] = array("StatValue" => $res["position"],
                                  "LastUpdate" => intval($res['timestamp']),
                                  "Source" => "coinmarketcap");
      $resdone++;
    }
    else {
      echo "Failed (JSON/position) ";
    }
    if (array_key_exists('change',$res)) {
      $tp["marketcapchange"] = array("StatValue" => $res["change"],
                                     "LastUpdate" => intval($res['timestamp']),
                                     "Source" => "coinmarketcap");
      $resdone++;
    }
    else {
      echo "Failed (JSON/change) ";
    }
    if (array_key_exists('supply',$res)) {
      $tp["marketcapsupply"] = array("StatValue" => $res["supply"],
                                     "LastUpdate" => intval($res['timestamp']),
                                     "Source" => "coinmarketcap");
      $resdone++;
    }
    else {
      echo "Failed (JSON/supply) ";
    }
    if (array_key_exists('market_cap',$res) && is_array($res['market_cap'])) {
      if (array_key_exists('btc',$res['market_cap'])) {
        $tp["marketcapbtc"] = array("StatValue" => $res['market_cap']['btc'],
                                    "LastUpdate" => intval($res['timestamp']),
                                    "Source" => "coinmarketcap");
        $resdone++;
      }
      else {
        echo "Failed (JSON/market_cap/btc) ";
      }
      if (array_key_exists('usd',$res['market_cap'])) {
        $tp["marketcapusd"] = array("StatValue" => $res['market_cap']['usd'],
                                    "LastUpdate" => intval($res['timestamp']),
                                    "Source" => "coinmarketcap");
        $resdone++;
      }
      else {
        echo "Failed (JSON/market_cap/usd) ";
      }
      if (array_key_exists('eur',$res['market_cap'])) {
        $tp["marketcapeur"] = array("StatValue" => $res['market_cap']['eur'],
                                    "LastUpdate" => intval($res['timestamp']),
                                    "Source" => "coinmarketcap");
        $resdone++;
      }
      else {
        echo "Failed (JSON/market_cap/eur) ";
      }
    }
    else {
      echo "Failed (JSON/market_cap) ";
    }
    if (array_key_exists('volume',$res) && is_array($res['volume'])) {
      if (array_key_exists('usd',$res['volume'])) {
        $tp["volumeusd"] = array("StatValue" => $res['volume']['usd'],
                                 "LastUpdate" => intval($res['timestamp']),
                                 "Source" => "coinmarketcap");
        $resdone++;
      }
      else {
        echo "Failed (JSON/volume/usd) ";
      }
      if (array_key_exists('eur',$res['volume'])) {
        $tp["volumeeur"] = array("StatValue" => $res['volume']['eur'],
                                 "LastUpdate" => intval($res['timestamp']),
                                 "Source" => "coinmarketcap");
        $resdone++;
      }
      else {
        echo "Failed (JSON/volume/eur) ";
      }
      if (array_key_exists('btc',$res['volume'])) {
        $tp["volumebtc"] = array("StatValue" => $res['volume']['btc'],
                                 "LastUpdate" => intval($res['timestamp']),
                                 "Source" => "coinmarketcap");
        $resdone++;
      }
      else {
        echo "Failed (JSON/volume/btc) ";
      }
    }
    else {
      echo "Failed (JSON/volume) ";
    }
    if ($resdone > 0) {
      if ($resdone == 9) {
        echo "OK";
      }
      else {
        echo "Partial";
      }
      echo " ($resdone values retrieved)\n";
    }
    else {
      echo "NOK\n";
    }
  }
  else {
    echo "Failed (JSON)\n";
  }
}
else {
  echo "Failed (GET)\n";
}

xecho("Submitting to web service: ");
$payload = array("thirdparties" => $tp);
$content = dmn_cmd_post('/thirdparties',$payload,$response);
var_dump($content);
if (strlen($content) > 0) {
  $content = json_decode($content,true);
  if (($response['http_code'] >= 200) && ($response['http_code'] <= 299)) {
    echo "Success (".$content['data']['thirdparties'].")\n";
  }
  elseif (($response['http_code'] >= 400) && ($response['http_code'] <= 499)) {
    echo "Error (".$response['http_code'].": ".$content['message'].")\n";
  }
}
else {
  echo "Error (empty result) [HTTP CODE ".$response['http_code']."]\n";
}

?>