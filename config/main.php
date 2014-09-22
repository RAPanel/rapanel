<?php
return array(
	'components' => array(
		'urlManager' => array(
			'rules' => array(
				'<m_:(rapanel)>' => array('<m_>//'),
				'<m_:(rapanel)>/<c_>' => array('<m_>/<c_>/index'),
				'<m_:(rapanel)>/<c_>/<a_>' => array('<m_>/<c_>/<a_>'),
			),
		),
	),
);