<?php

namespace App\Http\Controllers;

ini_set('max_execution_time','0');
use Illuminate\Http\Request;
use App\Model\Common;
use DB;
use Illuminate\Support\Facades\Log;


/**
 * Class CommonController
 *
 * @package App\Http\Controllers
 */
class CommonController extends Controller
{

    /**
     * @var string
     */
    protected $connection = 'mysql';


    /*
     * CommonController constructor.
     */
    /**
     * CommonController constructor.
     */
    public function __construct(){

        $this->Common = new Common();
    }

    /**
     *
     */
    public function test()
    {
        //Log::debug('An informational message.');
        $users = DB::connection('mysql2')->select("select * from user_roles");

        //Log::debug('Logged successfully.');

        //dd($users);

    }

    /**
     *Migration for customer Master table //completed
     */
    public function migrationForCustomerMaster()
    {
        $customer_details = array();

        $customers = DB::select("select COUNT(id) as no_of_records from customers");
        $total_no_of_records = $customers[0]->no_of_records;

        While($total_no_of_records != 0){

             $customers = DB::select("select * from customers where migration_flag='0' limit 1");

            foreach ($customers as $key => $value) {
                $customer_details[$key]['cm_id'] = empty($value->id) ? '' : $value->id;
                $customer_details[$key]['cm_name'] = empty($value->name) ? '' : $value->name;
                $customer_details[$key]['cm_email'] = empty($value->email) ? '' : $value->email;
                $customer_details[$key]['cm_phone'] = empty($value->phone) ? '' : $value->phone;
                $customer_details[$key]['cm_pincode'] = empty($value->pincode) ? '' : $value->pincode;
                $customer_details[$key]['cm_address'] = empty($value->address) ? '' : $value->address;
                $customer_details[$key]['cm_created_on'] = date('Y-m-d H:i:s');
                $customer_details[$key]['cm_weight'] = 0.0;
                $customer_details[$key]['cm_updated_by'] = 1;
                $customer_details[$key]['cm_updated_on'] = date('Y-m-d H:i:s');
                $customer_details[$key]['cm_created_by'] = 1;
                $customer_details[$key]['cm_is_deleted'] = 0;

            }

            $total_no_of_records--;

            $customer_details = json_decode(json_encode($customer_details), true);

            //remove die;
            if (!empty($customer_details)) {
                $result = $this->Common->InsertBulkData('customer_master', $customer_details, 'cm_id', 'mysql2');

                $update = $this->Common->dataUpdateOld('customers',['id'=>$customers[0]->id],[
                    'migration_flag' => 1],'mysql');

                if (is_array($result)) {
                    echo "<pre>";
                    print_r($result);
                } else {
                    echo $result;
                }
            }
            else{
                echo "empty customer";
            }
        }

    }

    /**
     *Migration for Delivery Config Master  //completed
     */
    public function migrationForDeliveryConfigMaster()
    {
        $delivery_config = DB::select("select * from delivery_config");

        foreach ($delivery_config as $key => $value){
            unset($delivery_config[$key]->id);
        }

        $delivery_config = json_decode(json_encode($delivery_config), true);

        //remove die
        //die;
        if(!empty($delivery_config)){
            $result = $this->Common->InsertBulkData('delivery_config',$delivery_config,'id', 'mysql2');
            print_r($result);
        }
    }


    /*
     * Migration for fit master   //dependency incomplete
     * */

    public function migrationForGarmentMaster()
    {

        $garmet_array = [['gm_type'=>'S',"gm_name"=>'Shirt','gm_updated_on'=> date('Y-m-d H:i:s'),'gm_created_on'=> date('Y-m-d H:i:s')],
                        ['gm_type'=>'T',"gm_name"=>'Trouser','gm_updated_on'=> date('Y-m-d H:i:s'),'gm_created_on'=> date('Y-m-d H:i:s')],
                        ['gm_type'=>'J',"gm_name"=>'Jacket','gm_updated_on'=> date('Y-m-d H:i:s'),'gm_created_on'=> date('Y-m-d H:i:s')],
                        ['gm_type'=>'W',"gm_name"=>'Waist','gm_updated_on'=> date('Y-m-d H:i:s'),'gm_created_on'=> date('Y-m-d H:i:s')]];

        echo "<pre>";
        print_r($garmet_array);die;
        //remove comment for migration from if
        if(!empty($garmet_array)){
            $result = $this->Common->InsertBulkData('garment_master',$garmet_array,'','mysql2');
            echo $result;
        }


    }

    /*
     * migration for hub  // incomplete
     * */
    public function migrationForHub(){

        $hub_info = array();
        $hubs = DB::select("select * from hubs");


        foreach ($hubs as $key => $value){
            $hub_info[$key]['hub_code']             = $value->hub_code;
            $hub_info[$key]['hub_name']             = $value->name;
            $hub_info[$key]['hub_address']          = $value->address;
            $hub_info[$key]['hub_contact_number']   = $value->contact_number;
            $hub_info[$key]['hub_email']            = $value->email;
            $hub_info[$key]['hub_city']             = $value->city;
            $hub_info[$key]['hub_pincode']          = $value->pincode;
            $hub_info[$key]['hub_gstin']            = $value->gstin;
            $hub_info[$key]['hub_state']            = $value->state;
            $hub_info[$key]['hub_type']             = $value->hub_type;
            $hub_info[$key]['hub_contact_person']   = '';
            $hub_info[$key]['hub_registered_name']  = '';
            $hub_info[$key]['hub_return_address']   = '';
            $hub_info[$key]['hub_return_city']      = '';
            $hub_info[$key]['hub_return_state']     = '';
            $hub_info[$key]['hub_return_pin']       = 1;
            $hub_info[$key]['hub_is_deleted']       = $value->status;

            //$hub_info[$key] = (object)$hub_info[$key];

        }

        $hub_info = json_decode(json_encode($hub_info), true);

        if(!empty($hub_info)){
            $result = $this->Common->InsertBulkData('hubs',$hub_info,'','mysql2');
            if(is_array($result)){
                echo "<pre>";
                print_r($result);die;
            }else{
                echo $result;
            }
        }

    }




}
