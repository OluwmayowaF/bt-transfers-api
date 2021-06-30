<!DOCTYPE html>
    <html lang="en-US">
    	<head>
    		<title>Transaction Update</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
    		<style type="text/css">
    			* {
    				box-sizing: border-box;
    				padding: 0;
    				margin: 0;
    			}
    			body {
    				background: white;
    				color: black !important;
    			}
    			.verifycode {
    				color: #0b273d;
    				background: white;
    				border: 1px solid #0b273d;
    				text-align: center;
    				padding: 10px 40px 10px 40px;
    				width: 50%;
    				margin: auto;
    				font-size: 15px;
    				font-weight: bold;
    			}
    			.welcome{
    				font-weight: bold;
    				text-align: center;
                    color: #0b273d !important;
    			}
    			.note{
    				font-weight: bold;
    				text-align: center;
                    color: black !important;
    			}
				.team{
                    font-weight: normal;
                    font-size: 12px;
                    text-align: center;
                    color: grey !important;
                }

    		</style>
    	</head>
    	<body>
    		<div>
    			
    		</div>
    		<h2 class="welcome">Transaction Update</h2>
    		<div class="note"><br><br>
    			 <h4>Hello {{ $details['user']['name']}}</h4>
    		</div><br><br>
    		
    		<div>
    			 <p class="note">{{$details['info']}}</p>
    		</div><br><br>
			
			<div>
                 <p class="team">if this mail is not authorized by you please kindly discard</p>
                 <p class="team" style="font-style: italic;">BT Transfer Team</p>
            </div>
    	</body>
    </html>