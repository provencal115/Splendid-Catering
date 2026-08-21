<?php
logout();
flash('success', 'You have been logged out.');
redirect(url());
