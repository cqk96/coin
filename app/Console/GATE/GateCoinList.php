<?php

namespace App\Console\GATE;


use App\Models\GateList;
use GateApi\Api\SpotApi;
use GateApi\Configuration;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class GateCoinList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GateCoinList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取gate新币列表';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $config = Configuration::getDefaultConfiguration()
            ->setKey('695f80c9d08c57d585f81b80e9606e66')
            ->setSecret('1a878d3b374543bfd88bd8b61ff22b30dd88a0e7051ed9973b0c2d2b798f7b87');


        $apiInstance = new SpotApi(
            new Client(),
            $config
        );
        $currency_pairs = 'MASA_USDT'; // string | A request can only query up to 50 currency pairs


        try {
            $result = $apiInstance->listCurrencyPairs();

          foreach ($result as $value){
                  $where=[
                      'name'=>$value['base']
                  ];
                  $data=[
                      'buy_start'=>date('Y-m-d H:i:s',$value['buy_start']),
                      'sell_start'=>date('Y-m-d H:i:s',$value['sell_start']),
                      'trade_status'=>$value['trade_status'],
                      'quote'=>$value['quote']
                  ];
                  if ($value['buy_start']==$value['sell_start']&&$value['sell_start']>time()){
                      $data['is_api_buy']=2;
                  }
                  GateList::query()->updateOrCreate($where, $data);

          }
        } catch (\Exception $e) {
            dd($e->getMessage());
            echo 'Exception when calling0 SpotApi->listCurrencies: ', $e->getMessage(), PHP_EOL;
        }
    }
}
