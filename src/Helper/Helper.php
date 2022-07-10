<?php

namespace App\Helper;
 use Symfony\Component\HttpFoundation\Response;

 class Helper{

     public const ALL = [250,500,1000,2000,5000];

     function check($number){
         $result = [];
         while($number>=self::ALL[0]){
             for($i=count(self::ALL)-1;true;$i--){
                 $diff = $number/self::ALL[$i];

                 if($diff>=1){
                     $result[] = self::ALL[$i];
                     $number = $number - self::ALL[$i];
                     break;
                 }
             }
         }
         if($number != 0){
             array_push($result,250);
         }

         return $result ;
     }

     function group($arr){
         $grouped = array_count_values($arr);
         $continue = true;
         while($continue === true){
             $continuation_required = false;
             foreach (array_reverse($grouped, true) as $key=>$value){
                 if($value!=1){
                     $product = $key * $value;
                     if(in_array($product,self::ALL)){
                         $continuation_required=true;
                         unset($grouped[$key]);
                         if(isset($grouped[$product])){
                             $grouped[$product]+=1;
                         }else{
                             $grouped[$product]=1;
                         }
                     }
                 }
             }
             if($continuation_required===false){
                 $continue=false;
             }
         }
         return $grouped;
     }

     public function buy(int $qty): Response
     {
        return new Response(json_encode($this->group($this->check($qty))));
     }
 }