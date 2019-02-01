<!DOCTYPE html>
<html>
<head>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>    
<script src="http://code.jquery.com/jquery-latest.js"></script>
<link rel="stylesheet" type="text/css" href="TestFramework.css">
</head>
<body > 
    <h2> Test Automation </h2>
<script>
    
    
    
    var resultDivNum = 0;
    
   /* window.onload = function()
    {	
        document.getElementById('vectors').value = <?php $file = file_get_contents( 'DefaultVectorList.txt' ); echo json_encode( $file ); ?>;
    }*/
    
    $(document).on('input', 'textarea', function () {
        $(this).outerHeight(38).outerHeight(this.scrollHeight); // 38 or '1em' -min-height
    });
    
    function newtab(mpdfile)
    {
        var testWin = window.open("../webfe/conformancetest.php?mpdurl="+mpdfile,"test");
        testWin.blur();
        return testWin;
    } 
    
    function testing()
    { 
        var ClearRef;
        //Test if the temp folder is empty, if not, then clean it.
        $.post(
               "checkempty.php",
               {path:'../webfe/temp'}
               ).done(function(response){
                console.log(response);
                if(response == "temp folder not empty")
                {  //Clean temp folder
                 $.post( 
                    "cleanup.php",
                    {path:'../webfe/temp/'},
                    function(response)
                    {
                        console.log(response);  
                    }
                 );
                }
            
            if( document.getElementById('Checkbox').checked)
               ClearRef=1;
            else 
               ClearRef=0;
              //Clean TestResults folder and References depending on the condition.
              $.post(
                     "cleanResults.php",
                     {flag: ClearRef}
                    ).done(function(response){
                        console.log(response);
                        console.log("TestResults cleaned");
                    if(response !== "References present")
                    {
                        document.getElementById('RefMsg').innerHTML=response;
                        document.getElementById('Checkbox').checked=true;
                        ajaxcall();   
                    }
                    else
                    {
                        document.getElementById('RefMsg').innerHTML=response;
                        ajaxcall();
                    }
                 });
                 
        });
        //Remove old Results division from the webpage
        for (var z=1;z<=resultDivNum;z++){
            if(!(document.getElementById('resultDiv'+z)== null))
                document.getElementById('resultDiv'+z).remove();
        }
        
        var  i=1, j=1;
        var vectorstr = document.getElementById('vectors').value;
        if (vectorstr!='')
        {
            var vectors = vectorstr.split("\n");
            console.log(vectors);
        }
       
        function ajaxcall()
        {
            if(i<=vectors.length)
            {
                var currentWin; // store the current Window
                if(!(document.getElementById('Checkbox').checked))
                {  
                   $.post(
                           "CountRef.php"
                         ).done(function(response){
                       var countRef= response;
                       console.log("Folders in References="+countRef);
                                      
                        if(i<= (countRef)){
                            currentWin = newtab(vectors[i-1]);  //process the current mpd file
                            document.getElementById('statusContent').innerHTML= "Running vector "+i;
                        }
                       else
                           alert("Testing stopped as there are no more References to compare"); 
                       });
                   
                }
                else{
                    currentWin = newtab(vectors[i-1]);  //process the current mpd file
                    document.getElementById('statusContent').innerHTML= "Running vector "+i;
                }
                
                // decide if we should rename and process the next test vector immediately
                if(document.getElementById('Pause').checked === false)
                {
                    //To check progress of Conformance Test and paste results into TestResults folder and References folder accordingly.             
                    $.post(
                        "second.php",
                        {length:vectors.length, path:'../webfe/temp', mpdURL:vectors[i-1]}
                    ).done(function(response){
                        var folder=response;
                        console.log(folder);
                        console.log("Successfully tested vector "+i);
                        $.post(
                             "CheckDiff.php",
                              {folder: folder}
                        ).done(function(response){
                            console.log(response);
                            // Success or failure is shown with 'right' or 'wrong' icons with links to errors.  
                            var id='resultDiv'+i; console.log(id);
                            var topn=232+18*i;
                            var top=topn + 'px';
                            if(response== "wrong"){
                                var div = '<div id= '+ id +' style="position: absolute;left:20px; top:'+top+';"></div>';
                                document.body.insertAdjacentHTML('beforeend', div);
                                var y = document.getElementById(id); 
                                y.innerHTML ='<a href="../webfe/TestResults/'+folder+'_diff.txt" target="_blank"> Check diff</a>';
                                $('#'+id).append('<img id="theImg" src="button_cancel.png" />');
                                document.getElementById('statusContent').innerHTML= "Completed vector "+j;
                            }
                            else{
                                var div = '<div id= '+ id +' style="position: absolute;left:100px; top:'+top+';"></div>';
                                document.body.insertAdjacentHTML('beforeend', div);
                                var y = document.getElementById(id); 
                                $('#'+id).prepend('<img id="theImg" src="right.jpg" />');
                                document.getElementById('statusContent').innerHTML= "Completed vector "+j;
                            }
                            if(vectors.length>j)
                                document.getElementById('statusContent').innerHTML= "Running vector "+(j+1);
                            j++;
                            resultDivNum=j;
                            i++;
                            ajaxcall();
                        });
                    });
                }
                else  // wait until user close the new tab
                {
                    var _flagCheck = setInterval(function() {
                        if (currentWin.closed) 
                        {
                            clearInterval(_flagCheck);
                            //To check progress of Conformance Test and paste results into TestResults folder and References folder accordingly.             
                            $.post(
                                "second.php",
                                {length:vectors.length, path:'../webfe/temp', mpdURL:vectors[i-1]}
                            ).done(function(response){
                                var folder=response;
                                console.log(folder);
                                console.log("Successfully tested vector "+i); 
                                $.post(
                                     "CheckDiff.php",
                                      {folder: folder}
                                ).done(function(response){
                                    console.log(response);
                                    // Success or failure is shown with 'right' or 'wrong' icons with links to errors.  
                                    var id='resultDiv'+i; console.log(id);
                                    var topn = 232 + 18*i;
                                    var top=topn + 'px';
                                    var div = '<div id= '+ id +' style="position: absolute;left:100px; top:'+top+';"></div>';
                                    document.body.insertAdjacentHTML('beforeend', div);
                                    var y = document.getElementById(id); 
                                    if(response== "wrong"){
                                        y.innerHTML ='<a href="../webfe/TestResults/'+folder+'_diff.txt" target="_blank"> Check differences</a>';
                                        $('#'+id).prepend('<img id="theImg" src="button_cancel.png" />');
                                        document.getElementById('statusContent').innerHTML= "Completed vector "+j;
                                    }
                                    else{
                                        $('#'+id).prepend('<img id="theImg" src="right.jpg" />');
                                        document.getElementById('statusContent').innerHTML= "Completed vector "+j;
                                    }
                                    if(vectors.length>j)
                                        document.getElementById('statusContent').innerHTML= "Running vector "+(j+1);
                                    j++;
                                    resultDivNum=j;
                                    i++;
                                    ajaxcall();
                                });
                            });
                        }
                    }, 10); // interval set at 10 milliseconds
                }
            }
            else  // Creating Reference results.
            {
                if (document.getElementById('Checkbox').checked)
                {
                    $.post(
                     "CreateRef.php"
                    ).done(function(response){
                        console.log("Referenced");
                  });

                }
            }
        }
    }
</script>



<br>
<p id="Testvectors">Test vectors :</p><br>
<textarea name="Text1" cols="110" rows="30" style="overflow:hidden" id='vectors' ></textarea>
<br><input type=button id="Start" value="Start Testing" onclick="testing()">  
<div id="tick" style="position: absolute; left: 900px"></div>
<p id="status">Status :</p>
<p id="statusContent"></p>
<p id="results">Results :</p>
<input type="checkbox" id="Checkbox">
<p id="ChecboxTitle">Create Reference</p>
<input type="checkbox" id="Pause">
<p id="PauseTitle">Continue only when the current test is closed by user</p>
<p id="RefMsg"></p>
</body>

</html>

