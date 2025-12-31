<?php

namespace App\Classes;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Auth;
use DB;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Simple\Manager;

use App\Models\User;
use App\Models\kyc_docs;
use App\Models\transactions_dmt;
use App\Models\transactions_aeps;
use App\Models\user_levels;
use App\Models\user_commissions;

use App\Models\recharge_commissions;
use App\Models\recharge_slabs;
use App\Models\transactions_recharges;


class WalletCalculation
{
    public function walletWithdrawFloat($user_id, $amount, $title, $detail, $txnid) {
        $maxRetries = 100;
        $retryCount = 0;
        
        while ($retryCount < $maxRetries) {
            try {
                return DB::transaction(function () use ($user_id, $amount, $title, $detail, $txnid) {
                    // Lock the user and wallet for update to prevent race conditions
                    $user = User::lockForUpdate()->find($user_id);
                    
                    if (!$user) {
                        throw new \Exception("User not found");
                    }
                    
                    // Get the wallet with lock
                    $wallet = $user->wallet()->lockForUpdate()->first();
                    
                    if (!$wallet) {
                        throw new \Exception("Wallet not found");
                    }
                    
                    // Check if wallet has sufficient balance
                    if ($wallet->balanceFloat < $amount) {
                        throw new \Exception("Insufficient balance");
                    }
                    
                    // Perform the withdrawal - Bavix will handle balance updates automatically
                    $transaction = $wallet->withdrawFloat($amount, [
                        'meta' => [
                            'Title' => $title,
                            'detail' => $detail,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    
                    // Update your custom balance field
                    Transaction::where('uuid', $transaction->uuid)->update([
                        'balance' => $wallet->fresh()->balance
                    ]);
                    
                    // Log successful transaction
                    Log::channel("wallet")->info("Withdrawal successful", [
                        'user_id' => $user_id,
                        'amount' => $amount,
                        'transaction_uuid' => $transaction->uuid,
                        'new_balance' => $wallet->fresh()->balanceFloat
                    ]);
                    
                    return $transaction->uuid;
                    
                }, 5); // 5 second timeout for the transaction
                
            } catch (\Illuminate\Database\QueryException $e) {
                $retryCount++;
                
                // Check if it's a deadlock or lock timeout
                if ($this->isDeadlockException($e) && $retryCount < $maxRetries) {
                    Log::channel("wallet")->warning("Deadlock detected, retrying...", [
                        'user_id' => $user_id,
                        'attempt' => $retryCount,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Wait a random amount of time before retrying (10-50ms)
                    usleep(rand(10000, 50000));
                    continue;
                }
                
                // Re-throw if not a deadlock or max retries reached
                throw $e;
                
            } catch (\Exception $e) {
                Log::channel("wallet")->error("Wallet withdrawal failed", [
                    'user_id' => $user_id,
                    'amount' => $amount,
                    'error' => $e->getMessage()
                ]);
                
                throw $e;
            }
        }
        
        throw new \Exception("Max retries exceeded for wallet withdrawal");
    }
    
    /**
     * Check if the exception is a deadlock
     */
    private function isDeadlockException(\Illuminate\Database\QueryException $e): bool {
        $deadlockCodes = [1213, 1205]; // MySQL deadlock error codes
        return in_array($e->getCode(), $deadlockCodes) || 
               str_contains($e->getMessage(), 'Deadlock') ||
               str_contains($e->getMessage(), 'Lock wait timeout');
    }
    // public function walletWithdrawFloat($user_id,$amount,$title,$detail,$txnid) {
    //     $i_balance = 1;
    //     walletWithdrawFloatGT:
    //     $userwallet = User::find($user_id);
    //     $wallet = $userwallet->wallet;
    //     $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
    //     if($balance_check){
              
    //     }else{
    //             $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
                
                
    //     }
    //     if ($balance_check) {
    //         if($balance_check->balance != $userwallet->wallet->balance){
    //             Log::channel("balancemissmatch")->info("========");
    //             Log::channel("balancemissmatch")->info($wallet->id);
    //             Log::channel("balancemissmatch")->info($balance_check->balance);
    //             Log::channel("balancemissmatch")->info($userwallet->wallet->balance);
    //             if($i_balance >= 100){
    //                 exit;
    //                 return "0";
    //             }else{
    //                 $i_balance++;
    //                 goto walletWithdrawFloatGT;
    //             }
    //         }
    //     }
    //     $txn_wl = $wallet->withdrawFloat($amount,[
    //             'meta' => [
    //                 'Title' => $title,
    //                 'detail' => $detail,
    //                 'transaction_id' => $txnid,
    //             ]
    //         ]);
                
    //     $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
        
    //     return $txn_wl->uuid;
    // }
    
    public function walletDepositFloat($user_id, $amount, $title, $detail, $txnid) {
        $maxRetries = 100;
        $retryCount = 0;
        
        Log::channel("wallet")->info("Starting wallet deposit", ['user_id' => $user_id, 'amount' => $amount]);
        
        while ($retryCount < $maxRetries) {
            try {
                return DB::transaction(function () use ($user_id, $amount, $title, $detail, $txnid) {
                    // Lock the user and wallet for update to prevent race conditions
                    $user = User::lockForUpdate()->find($user_id);
                    
                    if (!$user) {
                        throw new \Exception("User not found");
                    }
                    
                    // Get the wallet with lock
                    $wallet = $user->wallet()->lockForUpdate()->first();
                    
                    if (!$wallet) {
                        throw new \Exception("Wallet not found");
                    }
                    
                    // Validate amount
                    if ($amount <= 0) {
                        throw new \Exception("Deposit amount must be positive");
                    }
                    
                    // Store initial balance for logging
                    $initialBalance = $wallet->balanceFloat;
                    
                    // Perform the deposit - Bavix will handle balance updates automatically
                    $transaction = $wallet->depositFloat($amount, [
                        'meta' => [
                            'Title' => $title,
                            'detail' => $detail,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    
                    // Update your custom balance field
                    Transaction::where('uuid', $transaction->uuid)->update([
                        'balance' => $wallet->fresh()->balance
                    ]);
                    
                    // Log successful transaction
                    Log::channel("wallet")->info("Deposit successful", [
                        'user_id' => $user_id,
                        'amount' => $amount,
                        'transaction_uuid' => $transaction->uuid,
                        'initial_balance' => $initialBalance,
                        'new_balance' => $wallet->fresh()->balanceFloat
                    ]);
                    
                    return $transaction->uuid;
                    
                }, 5); // 5 second timeout for the transaction
                
            } catch (\Illuminate\Database\QueryException $e) {
                $retryCount++;
                
                // Check if it's a deadlock or lock timeout
                if ($this->isDeadlockException($e) && $retryCount < $maxRetries) {
                    Log::channel("wallet")->warning("Deadlock detected during deposit, retrying...", [
                        'user_id' => $user_id,
                        'attempt' => $retryCount,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Wait a random amount of time before retrying (10-50ms)
                    usleep(rand(10000, 50000));
                    continue;
                }
                
                // Re-throw if not a deadlock or max retries reached
                throw $e;
                
            } catch (\Exception $e) {
                Log::channel("wallet")->error("Wallet deposit failed", [
                    'user_id' => $user_id,
                    'amount' => $amount,
                    'error' => $e->getMessage()
                ]);
                
                throw $e;
            }
        }
        
        throw new \Exception("Max retries exceeded for wallet deposit");
    }
    
    // public function walletDepositFloat($user_id,$amount,$title,$detail,$txnid) {
    //     $i_balance = 1;
    //     Log::info($user_id);
    //     walletDepositFloatGT:
    //     $userwallet = User::find($user_id);
    //     $wallet = $userwallet->wallet;
    //     $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
    //     if($balance_check){
              
    //     }else{
    //             $balance_check = Transaction::where('wallet_id', $wallet->id)->orderBy('id','desc')->first();
                
                
    //     }
    //     if ($balance_check) {
    //         if($balance_check->balance != $userwallet->wallet->balance){
    //             Log::channel("balancemissmatch")->info("========");
    //             Log::channel("balancemissmatch")->info($wallet->id);
    //             Log::channel("balancemissmatch")->info($balance_check->balance);
    //             Log::channel("balancemissmatch")->info($userwallet->wallet->balance);
    //             if($i_balance >= 100){
    //                 exit;
    //                 return "0";
    //             }else{
    //                 $i_balance++;
    //                 goto walletDepositFloatGT;
    //             }
                
    //         }
    //     }
    //     $txn_wl = $wallet->depositFloat($amount,[
    //             'meta' => [
    //                 'Title' => $title,
    //                 'detail' => $detail,
    //                 'transaction_id' => $txnid,
    //             ]
    //         ]);
                
    //     $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
        
    //     return $txn_wl->uuid;
    // }
    
    public function rechargeSlab($transactions_id) {
        $tds_par = 2;
        $txn = transactions_recharges::where('transaction_id',$transactions_id)->first();
        $amount = $txn->amount;
        $user_id = $txn->user_id;
        $user = User::with('userLevel')->find($user_id);
        
        //Retailer
        $op_comm = recharge_commissions::where('slab_id',$user->recharge_slab)->where('op_id',$txn->op_id)->first();
        if($txn){
            $comm = $op_comm->commission;
            $comm_type = $op_comm->commission_type;
            
            if($comm_type == 1){
                $total_comm = $amount * $comm / 100;
            }else{
               $total_comm = $comm;
            }
        }
        
        $tds = $total_comm * $tds_par / 100;
        $final_comm = $total_comm - $tds;
        
        //distributor
        $user_id_dist = $user->userLevel->toplevel_id;
        $user_dist = User::find($user_id_dist);
        
        $op_comm_dist = recharge_commissions::where('slab_id',$user_dist->recharge_slab)->where('op_id',$txn->op_id)->first();
        if($txn){
            $comm_dist = $op_comm_dist->commission;
            $comm_type_dist = $op_comm_dist->commission_type;
            
            if($comm_type_dist == 1){
                $total_comm_dist = $amount * $comm_dist / 100;
            }else{
               $total_comm_dist = $comm_dist;
            }
        }
        
        $tds_dist = $total_comm_dist * $tds_par / 100;
        $final_comm_dist = $total_comm_dist - $tds_dist;
        
        return ['user_id'=>$user_id,'comm' => $final_comm, 'tds' => $tds, 'comm_dist' => $final_comm_dist, 'tds_dist' => $tds_dist,'user_id_dist'=>$user_id_dist];
        
    }
    
    public function retailorAeps($transactions_id) {
        
        $txn = transactions_aeps::where('transaction_id',$transactions_id)->first();
        $amount = $txn->amount;
        $fee = $txn->fee;
        $tds_par = 2;
        if($txn->transfer_type == 'cash_withdrawal'){
            
        
            if($amount >= 1 && $amount <= 300){
                $commission_rate = 0;
                $commission_rate_type = 1;//1 = flat and 2 = varibale
            }elseif($amount >= 301 && $amount <= 1000){
                $commission_rate = 0.5;
                $commission_rate_type = 1;//1 = flat and 2 = varibale
            }elseif($amount >= 1001 && $amount <= 1500){
                $commission_rate = 1;
                $commission_rate_type = 1;//1 = flat and 2 = varibale
            }elseif($amount >= 1501 && $amount <= 2000){
                $commission_rate = 3;
                $commission_rate_type = 1;//1 = flat and 2 = varibale
            }elseif($amount >= 2001 && $amount <= 2500){
                $commission_rate = 4;
                $commission_rate_type = 1;//1 = flat and 2 = varibale
            }elseif($amount >= 2501 && $amount <= 3000){
                $commission_rate = 5;
                $commission_rate_type = 1;//1 = flat and 2 = varibale
            }elseif($amount >= 3001 && $amount <= 8000){
                $commission_rate = 7;
                $commission_rate_type = 1;//1 = flat and 2 = varibale
            }elseif($amount >= 8001 && $amount <= 10000){
                $commission_rate = 9;
                $commission_rate_type = 1;//1 = flat and 2 = varibale
            }else{
                $commission_rate = 0;
                $commission_rate_type = 1;//1 = flat and 2 = varibale
            }
        }elseif($txn->transfer_type == 'mini_statement'){
            $commission_rate = 0.4;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }else{
            $commission_rate = 0;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }
        
        if($commission_rate_type == 1){
            $total_comm = $commission_rate;
        }else{
            $total_comm = $amount * $commission_rate / 100;
        }
        
        $tds = $total_comm * $tds_par / 100;
        $final_comm = $total_comm - $tds;
        return ['comm' => $final_comm, 'tds' => $tds];
        
    }
    
    public function distributorAeps($transactions_id) {
        
        $txn = transactions_aeps::where('transaction_id',$transactions_id)->first();
        $amount = $txn->amount;
        $fee = $txn->fee;
        $tds_par = 2;
        if($amount >= 1 && $amount <= 300){
            $commission_rate = 0;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }elseif($amount >= 301 && $amount <= 1000){
            $commission_rate = 0;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }elseif($amount >= 1001 && $amount <= 1500){
            $commission_rate = 1;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }elseif($amount >= 1501 && $amount <= 2000){
            $commission_rate = 1;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }elseif($amount >= 2001 && $amount <= 2500){
            $commission_rate = 1;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }elseif($amount >= 2501 && $amount <= 3000){
            $commission_rate = 1;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }elseif($amount >= 3001 && $amount <= 8000){
            $commission_rate = 1;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }elseif($amount >= 8001 && $amount <= 10000){
            $commission_rate = 1;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }else{
            $commission_rate = 0;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }
        
        if($commission_rate_type == 1){
            $total_comm = $commission_rate;
        }else{
            $total_comm = $amount * $commission_rate / 100;
        }
        
        $tds = $total_comm * $tds_par / 100;
        $final_comm = $total_comm - $tds;
        return ['comm' => $final_comm, 'tds' => $tds];
        
    }
    
    public function retailorUpi($transactions_id) {
        
        $txn = transactions_dmt::where('transaction_id',$transactions_id)->first();
        $amount = $txn->amount;
        $fee = $txn->fee;
        $tds_par = 2;
        if($amount >= 1 && $amount <= 500){
            $commission_rate = 0;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }elseif($amount >= 501 && $amount <= 1000){
            $commission_rate = 0.30;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }elseif($amount >= 1001 && $amount <= 1500){
            $commission_rate = 0.48;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }elseif($amount >= 1501 && $amount <= 2000){
            $commission_rate = 0.48;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }elseif($amount >= 2001 && $amount <= 2500){
            $commission_rate = 0.48;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }else{
            $commission_rate = 0.48;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }
        
        if($commission_rate_type == 1){
            $total_comm = $commission_rate;
        }else{
            $total_comm = $amount * $commission_rate / 100;
        }
        
        $tds = $total_comm * $tds_par / 100;
        $final_comm = $total_comm - $tds;
        return ['comm' => $final_comm, 'tds' => $tds];
        
    }
    
    public function distributorUpi($transactions_id) {
        
        $txn = transactions_dmt::where('transaction_id',$transactions_id)->first();
        $amount = $txn->amount;
        $fee = $txn->fee;
        $tds_par = 2;
        if($amount >= 1 && $amount <= 500){
            $commission_rate = 0;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }elseif($amount >= 501 && $amount <= 1000){
            $commission_rate = 0.18;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }elseif($amount >= 1001 && $amount <= 1500){
            $commission_rate = 0.18;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }elseif($amount >= 1501 && $amount <= 2000){
            $commission_rate = 0.18;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }elseif($amount >= 2001 && $amount <= 2500){
            $commission_rate = 0.18;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }else{
            $commission_rate = 0.18;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }
        
        if($commission_rate_type == 1){
            $total_comm = $commission_rate;
        }else{
            $total_comm = $amount * $commission_rate / 100;
        }
        
        $tds = $total_comm * $tds_par / 100;
        $final_comm = $total_comm - $tds;
        return ['comm' => $final_comm, 'tds' => $tds];
        
    }
    
    public function retailorDmt($transactions_id) {
        
        $txn = transactions_dmt::where('transaction_id',$transactions_id)->first();
        $amount = $txn->amount;
        $fee = $txn->fee;
        $tds_par = 2;
        if($amount >= 1 && $amount <= 500){
            $commission_rate = 0;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }else{
            if($txn->api_id == 4 || $txn->api_id == 0){
                $commission_rate = 0.40;
            }else{
                $commission_rate = 0.48;
            }
            //$commission_rate = 0.48;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }
        
        if($commission_rate_type == 1){
            $total_comm = $commission_rate;
        }else{
            $total_comm = $amount * $commission_rate / 100;
        }
        
        $tds = $total_comm * $tds_par / 100;
        $final_comm = $total_comm - $tds;
        return ['comm' => $final_comm, 'tds' => $tds];
        
    }
    
    public function distributorDmt($transactions_id) {
        
        $txn = transactions_dmt::where('transaction_id',$transactions_id)->first();
        $amount = $txn->amount;
        $fee = $txn->fee;
        $tds_par = 2;
        if($amount >= 1 && $amount <= 500){
            $commission_rate = 0;
            $commission_rate_type = 1;//1 = flat and 2 = varibale
        }else{
            $commission_rate = 0.18;
            $commission_rate_type = 2;//1 = flat and 2 = varibale
        }
        
        if($commission_rate_type == 1){
            $total_comm = $commission_rate;
        }else{
            $total_comm = $amount * $commission_rate / 100;
        }
        
        $tds = $total_comm * $tds_par / 100;
        $final_comm = $total_comm - $tds;
        return ['comm' => $final_comm, 'tds' => $tds];
        
    }
    
    public function distributorAeps1($transactions_id) {
        return 'done';
        $txn = transactions_aeps::where('transaction_id',$transactions_id)->first();
        $amount = $txn->amount;
        $tds_par = 2;
        $total_comm = $amount * 0.2 / 100;
        $tds = $total_comm * $tds_par / 100;
        $final_comm = $total_comm - $tds;
        sleep(rand(2,7));
        $topup_user = user_levels::where('user_id',$txn->user_id)->first();
        $user_id = $topup_user->toplevel_id;
        $user = User::find($user_id);
        
         // Create a wallet for the user
        $wallet = Wallet::findOrNew($user);
        
        // Check if the user has a wallet
        if (!$wallet) {
            // If the user doesn't have a wallet, create one
            $wallet = Wallet::create(['user_id' => $userId]);
        } else {
            // If the user already has a wallet, fetch it
            $wallet = $user->wallet;
        }
        
        $txnid = $this->txnId('COMM');
        // Deposit funds into the user's wallet
        
        $txn_wl = $wallet->depositFloat($final_comm,[
            'meta' => [
                'Title' => 'Commission',
                'detail' => 'AEPS Commission '.$txnid,
                'transaction_id' => $txnid,
            ]
        ]);
        
        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
        
        $ins = new user_commissions();
        $ins->user_id = $user_id;
        $ins->transaction_id = $txnid;
        $ins->total_amount = $total_comm;
        $ins->amount = $final_comm;
        $ins->tds = $tds;
        $ins->tds_par = $tds_par;
        $ins->wallets_uuid = $wallet->uuid;
        $ins->ref_transaction_id = $transactions_id;
        $ins->save();
        
        return 'done';
    }
    
    public function retailerAeps1($transactions_id) {
        return 'done';
        $txn = transactions_aeps::where('transaction_id',$transactions_id)->first();
        $amount = $txn->amount;
        $tds_par = 2;
        $total_comm = $amount * 1 / 100;
        $tds = $total_comm * $tds_par / 100;
        $final_comm = $total_comm - $tds;
        sleep(rand(2,7));
        
        $user_id = $txn->user_id;
        $user = User::find($user_id);
        
         // Create a wallet for the user
        $wallet = Wallet::findOrNew($user);
        
        // Check if the user has a wallet
        if (!$wallet) {
            // If the user doesn't have a wallet, create one
            $wallet = Wallet::create(['user_id' => $userId]);
        } else {
            // If the user already has a wallet, fetch it
            $wallet = $user->wallet;
        }
        
        $txnid = $this->txnId('COMM');
        // Deposit funds into the user's wallet
        
        $txn_wl = $wallet->depositFloat($final_comm,[
            'meta' => [
                'Title' => 'Commission',
                'detail' => 'AEPS Commission '.$txnid,
                'transaction_id' => $txnid,
            ]
        ]);
        
        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
        
        $ins = new user_commissions();
        $ins->user_id = $user_id;
        $ins->transaction_id = $txnid;
        $ins->total_amount = $total_comm;
        $ins->amount = $final_comm;
        $ins->tds = $tds;
        $ins->tds_par = $tds_par;
        $ins->wallets_uuid = $wallet->uuid;
        $ins->ref_transaction_id = $transactions_id;
        $ins->save();
        
        return 'done';
    }
    
    public function txnId($prifix=null) {

        $day = now()->format('D');
        $txn = rand(100000, 999999);
        $txn_id = strtoupper($day . $prifix . date('ymd') . $txn);
        
        $check_trans = transactions_dmt::where('transaction_id',$txn_id)->first();
        $check_user_commissions = user_commissions::where('transaction_id',$txn_id)->first();
        
        if($check_trans || $check_user_commissions) {
            return $this->txnId($prifix=null);
        }
        else {
            return $txn_id;
        }
    }
    
}
?>