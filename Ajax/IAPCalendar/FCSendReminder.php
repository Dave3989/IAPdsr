<?php
/*
AnimaSola says:	
December 6, 2012 at 11:09 pm	

Great plug-in Ryan! Some questions:

1) may I know the proper array argument/parameter for schedule_reminder?
2) can schedule_reminder be called from functions.php?

I have a sign-up form where people choose a date. They are supposed to get an e-mail reminder a day before that chosen date. And your plugin is perfect since it’s lightweight and effective.

However, I want to automatically add reminders whenever users sign-up and I figured that I use a filter for the sign-up to call a function which then calls your send_ereminders. Is that the right approach?

Salamat bai!
Reply	

    Ryann says:	
    December 7, 2012 at 10:46 am	

    Hey man, thanks for checking out Email Reminder plugin :)

    Anyway, about your questions:

    1) proper argument for PDER_Admin::schedule_reminder().

    To see how the function is used, see PDER_Admin::process_submissions() function. Basically, the function is just passed the submitted $_POST data where the needed values for scheduling the reminder are stored in $_POST['pder'] array. To see the values for this array, take a look at the variable $empty_fields in the file PDER_Admin.php line #37. Since you are creating a new reminder, you only need to populate ‘reminder’, ‘email’, ‘time’, and ‘date’. ‘Reminder’ is just text. ‘time’ can be in the format HH:MM i.e. 15:30 or 3:30pm. ‘date’ should be in ‘YYYY-MM-DD’ format.

    Question 2) can I call schedule_reminder() from functions.php?

    Well, it should be. To use, you can try pasting the following to your functions.php file:
*/
    $my_reminder = array('pder' => array(
    	'reminder' => 'Type your reminder here',
    	'email' => 'youremail@gmail.com',
    	'time' => '3:00 pm',
    	'date' => '2012-12-25'
    ));

    $pder_admin_obj = new PDER_Admin();
    $pder_admin_obj->schedule_reminder( $my_reminder );
/*
    NOTE: this is untested code and I did not mean for the function to be used this way. Other than that, hope it works! <?php
*/


?>