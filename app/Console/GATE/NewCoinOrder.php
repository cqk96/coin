<?php

namespace App\Console\GATE;


use App\Models\GateList;
use App\Models\GateMessage;
use GateApi\Api\SpotApi;
use GateApi\Configuration;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class NewCoinOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'NewCoinOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'gate新币下单';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //todo 改成数据库获取
        $coin = GateList::query()
            ->select('name')
            ->where('buy_start', '>', date('Y-m-d H:i:s'))
            ->where('quote', 'USDT')
            ->orderBy('buy_start', 'asc')
            ->get()
            ->first();
        $config = Configuration::getDefaultConfiguration()
            ->setKey('695f80c9d08c57d585f81b80e9606e66')
            ->setSecret('1a878d3b374543bfd88bd8b61ff22b30dd88a0e7051ed9973b0c2d2b798f7b87');


        $apiInstance = new SpotApi(
            new Client(),
            $config
        );
        $this->toOrder($coin->name,$apiInstance);

    }

    public function toOrder($name,$apiInstance){

        $currency = $name.'_USDT';

        try {

            //todo 金额改成设置

            $data = [
                'type'          => 'market',
                'time_in_force' => 'ioc',
                'currency_pair' => $currency,
                'amount'        => '50',
                'side'          => 'buy',
            ];
            $order = new \GateApi\Model\Order($data);
            $result = $apiInstance->createOrder($order);
            $price = json_decode($result)->avg_deal_price;
            //买入成功 获取余额
            $associate_array['currency'] = $name;
            $result = $apiInstance->listSpotAccounts($associate_array);
            $available = json_decode($result[0])->available;

            // 10%涨跌就卖出
            $PriceUp = $price * 1.2;
            $PriceDown = $price * 0.9;
            $isSale = true;

            GateMessage::query()->insert(['message'=>date('Y-m-d H:i:s').'购买完成等待卖出price' . $price]);
            while ($isSale) {
                $associate_array['currency_pair'] = $currency;
                $tickers = $apiInstance->listTickers($associate_array);
                $nowPrice = json_decode($tickers[0])->lowest_ask;
                dump(date('Y-m-d H:i:s').'买入价：' . $price . '当前价格' . $nowPrice . '卖出目标价格' . '[' . $PriceDown .'|'. $PriceUp . ']');
                if ($nowPrice > $PriceUp || $nowPrice < $PriceDown) {

                    $data = [
                        'type'          => 'market',
                        'time_in_force' => 'ioc',
                        'currency_pair' => $currency,
                        'amount'        => $available,
                        'side'          => 'sell',
                    ];
                    $order = new \GateApi\Model\Order($data);
                    $result = $apiInstance->createOrder($order);
                    $SalePrice = json_decode($result)->avg_deal_price;

                    $isSale = false;
                }
            }
            GateMessage::query()->insert(['message'=>$currency . '买入价' . $price . '---卖出价' . $SalePrice]);
            return true;
        } catch (\Exception $e) {
            if (date('i')>2){
                GateMessage::query()->insert(['message'=>$e->getMessage()]);
                return true;
            }
            $errorString = "has no latest price, please try later";
            if (strpos($e->getMessage(), $errorString) !== false) {
                dump(date('Y-m-d H:i:s').$e->getMessage());
                $this->toOrder($name,$apiInstance);
            }
            $limitError='Request Rate Limit Exceeded (007)';
            if (strpos($e->getMessage(), $limitError) !== false) {
                dump(date('Y-m-d H:i:s').$e->getMessage());
                sleep(1);
                $this->toOrder($name,$apiInstance);
            }
            echo 'Exception when calling0 SpotApi->listCurrencies: ', $e->getMessage(), PHP_EOL;
        }
    }
}
