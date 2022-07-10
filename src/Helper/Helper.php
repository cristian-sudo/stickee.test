<?php

namespace App\Helper;
 use Symfony\Component\HttpFoundation\Response;

 class Helper{

     public const ALL = [250,500,1000,2000,5000];

     function check($number,$newArray): array
     {
         $result = [];
         while($number>=$newArray[0]){
             for($i=count($newArray)-1;true;$i--){
                 $diff = $number/$newArray[$i];

                 if($diff>=1){
                     $result[] = $newArray[$i];
                     $number = $number - $newArray[$i];
                     break;
                 }
             }
         }
         if($number != 0){
             $result[] = $newArray[0];
         }

         return $result ;
     }

     function group($arr): array
     {
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
         $newArray = self::ALL;
         sort($newArray);
        return new Response(json_encode($this->group($this->check($qty,$newArray))));
     }
 }