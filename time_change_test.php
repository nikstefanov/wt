<?php
	//� 3:00 ���� �������� �� 27 ���� 2016 �. - ������ ���������� ����������� �� 1 ��� ������.
	$date1 = date_create_from_format("Y-m-d,H:i:s", "2016-03-27,00:00:00");
	$date2 = date_create_from_format("Y-m-d,H:i:s", "2016-03-28,00:00:00");
	echo("date 2 - date 1: ".($date2->getTimestamp() - $date1->getTimestamp())."s\n");
	$one_day = new DateInterval("P1D");
	$date1->add($one_day);
	echo("date 2 - (date 1 + 1 day): ".($date2->getTimestamp() - $date1->getTimestamp())."s\n");
?>