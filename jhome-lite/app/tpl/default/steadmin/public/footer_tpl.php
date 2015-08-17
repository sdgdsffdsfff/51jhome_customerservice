 <!-- footer --> 
  <footer id="footer">
   <div class="text-center padder clearfix"> 
    <p> <small>&copy; 2014 结邻公社</small> </p> 
   </div> 
  </footer> 
  <!-- / footer --> 
  <!-- Bootstrap --> 
  <!-- app --> 
<?php
	getJs(array('content/global','content/facebox','content/msgbox'));//,'content/slider/bootstrap-slider'
 if (isset($Document['footerjs'])&&$Document['footerjs']){
	getJs($Document['footerjs']);
 }
?>
<div style="display:none"><img src="<?php echo IMG_PATH?>content/gb_tip_layer.png" style="display:none" /></div>
 </body>
</html>