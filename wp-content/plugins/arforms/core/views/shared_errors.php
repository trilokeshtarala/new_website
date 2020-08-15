<?php 
if (isset($message) && $message != ''){ if(is_admin()){ ?>
    <script type="text/javascript" language="javascript"> setTimeout( function(){ success_msg(); },10); </script>
    <div id="success_message" class="arf_success_message">
     <div class="message_descripiton">
        <div style="float: left; margin-right: 15px;"><?php } echo $message; if(is_admin()){ ?></div>
        <div class="message_svg_icon">
            <svg style="height: 14px;stroke-width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M6.075,14.407l-5.852-5.84l1.616-1.613l4.394,4.385L17.181,0.411
    l1.616,1.613L6.392,14.407H6.075z"></path></svg>
        </div>
        </div>
    </div><?php } }

if (isset($message_notRquireFeild) && $message_notRquireFeild!= ''){ if(is_admin()){ ?><div id="warning_message" class="warning_message" style="padding:5px; margin-bottom:0px; margin-top:15px;float:left;display:block !important;"><?php } echo $message_notRquireFeild; if(is_admin()){ ?></div><?php } } ?>

<?php if( isset($errors) && is_array($errors) && count($errors) > 0 ){ ?>
	<?php
    foreach ($errors as $error) {
        ?><script type="text/javascript" language="javascript"> setTimeout( function(){ error_msg(); },10); </script>
        <div id="error_message" class="arf_error_message">
        <div class="message_descripiton">
        	<div style="float: left; margin-right: 15px;"><?php echo stripslashes($error); ?></div>
        	<div class="message_svg_icon">
        		<svg style="height: 14px;stroke-width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></svg>
        	</div>
        </div>
        </div>
    <?php } ?>

<?php } ?>