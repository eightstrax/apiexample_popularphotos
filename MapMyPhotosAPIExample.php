<?php

    /*******************
        USERNAME, PASSWORD AND DETAILS ABOUT THE MAPMYPHOTO API THAT YOU WILL USE
    *******************/

    $mmpUsernameOrEmail         = "lachlan";
    $mmpPassword                = "***********************";

    $baseAPIUrl                 = "https://api.mapmyphotos.net/";
    $apiRequestContentType      = "application/json";
    $apiRequestMethod           = "POST";

    $apiEndpoint_SubmitLogin    = "/login/submit";
    $apiEndpoint_PopularPhotos  = "/stats/getpopularphotos";

    $apiAuthToken = null;

    $arryPhotos = array();
    $userObj = null;

    try{

        /*******************
            MAKE AN API CALL TO LOGIN AND RETRIEVE THE AUTH TOKEN FROM THE RESPONSE
        *******************/
        $requestData = array
        (
            "UserName" => $mmpUsernameOrEmail, 
            "Password" => $mmpPassword
        );   

        $dataJsonString = json_encode($requestData);                                                                                   
                                                                                                                            
        $ch = curl_init($baseAPIUrl.$apiEndpoint_SubmitLogin);                                                                      
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $apiRequestMethod);                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJsonString);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array
            (                                                                          
                'Content-Type: '.$apiRequestContentType,                                                                                
                'Content-Length: '.strlen($dataJsonString)
            )                                                                       
        );                                                                                                                   
                                                                                                                            
        $submitLoginResponse = curl_exec($ch);
        $submitLoginObjResponse = json_decode($submitLoginResponse);

        if
        (
            isset($submitLoginObjResponse) && 
            $submitLoginObjResponse->Succeeded
        )
        {
            //Login Success
            $userObj = $submitLoginObjResponse->Data;
            $apiAuthToken = $submitLoginObjResponse->AuthToken;
        }
        else
        {
            //Login Failure
            echo "<span style='color:red;'>Login to MapMyPhotos failed: ".$submitLoginObjResponse->ErrorMessage."</span>";
            exit();
        }
        
        // close cURL resource, and free up system resources
        curl_close($ch);

        /*******************
            MAKE AN API CALL TO OBTAIN MOST POPULAR PHOTOS, LAST 31 DAYS
        *******************/
        $requestData = array
        (
            "StartDate" => date("j M Y H:i:s", time() - (31 * 24 * 60 * 60)), 
            "EndDate" => date("j M Y H:i:s"),
            "AuthToken" => $apiAuthToken
        );   

        $dataJsonString = json_encode($requestData);                                                                                   
                                                                                                                            
        $ch = curl_init($baseAPIUrl.$apiEndpoint_PopularPhotos);                                                                      
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $apiRequestMethod);                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJsonString);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array
            (                                                                          
                'Content-Type: '.$apiRequestContentType,                                                                                
                'Content-Length: '.strlen($dataJsonString)
            )                                                                       
        );     
                                                                                                              
                                                                                                                            
        $popPhotosResponse = curl_exec($ch);
        $popPhotosObjResponse = json_decode($popPhotosResponse);

        if
        (
            isset($popPhotosObjResponse) && 
            $popPhotosObjResponse->Succeeded
        )
        {
            //Get Popular Photos Success
            $arryPhotos = $popPhotosObjResponse->Data;
        }
        else
        {
            //Get Popular Photos Failure
            echo "<span style='color:red;'>Request MapMyPhotos failed: ".$popPhotosObjResponse->ErrorMessage."</span>";
            exit();
        }
        
        // close cURL resource, and free up system resources
        curl_close($ch);
    }
    catch(Exception $ex)
    {
        echo "<span style='color:red;'>Exception caught: ".$ex->getMessage()."</span>";
        exit();
    }


    /*******************
        ECHO OUT THE PHOTOS AND PROFILE INFORMATION IN HTML FORMAT
    *******************/

    if($userObj != null && count($arryPhotos) > 0)
    {
        //PROFILE INFORMATION
        echo "<div style='display:block;float:left;'><img style='width:100px;height:100px;' src='".$userObj->ProfilePicture."' /></div>";
        echo "<div style='display:block;float:left;line-height:50px;margin-left:20px;'><h4>Popular photos for ".$userObj->FirstName." ".
                $userObj->LastName.", last 31 days</h4></div><div style='clear:both;'></div>";

        echo "<hr></hr>";

        //POPULAR PHOTOS
        foreach($arryPhotos as $thisPhoto)
        {
            echo    "<div style='display:block;float:left;width:150px;min-height:250px;padding:5px;'>".
                        "<center>".
                            "<img style='width:150px;height:150px;' src='".$thisPhoto->ThmSrc."' />".
                            $thisPhoto->Caption."<br/>".
                            "Taken in ".$thisPhoto->City."<br/>".
                            "Index: ".$thisPhoto->StatCount.
                        "</center>".
                    "</div>";
        }

        echo "<div style='clear:both;'></div>";
    }
    else
    {
        echo "<span style='color:red;'>Something went wrong.</span>";
        exit();
    }

?>