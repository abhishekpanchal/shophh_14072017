var config = {
	map: {
		'*': {
			'altima/note': 'Altima_Lookbookslider/js/jquery/slider/jquery-ads-note',
			'altima/impress': 'Altima_Lookbookslider/js/report/impress',
			'altima/clickslide': 'Altima_Lookbookslider/js/report/clickbanner',
		},
	},
	paths: {
		'altima/cycle': 'Altima_Lookbookslider/js/jquery.cycle2',
		'altima/cyclecarousel': 'Altima_Lookbookslider/js/jquery.cycle2.carousel',
		'altima/cyclecenter': 'Altima_Lookbookslider/js/jquery.cycle2.center.min',
		'altima/cycleAddEffects': 'Altima_Lookbookslider/js/jquery.cycle2.addEffects',
		'altima/cycleflip': 'Altima_Lookbookslider/js/jquery.cycle2.flip.min',
		'altima/actual': 'Altima_Lookbookslider/js/jquery.actual',
		'altima/hotspots': 'Altima_Lookbookslider/js/hotspots',
		'altima/flexslider': 'Altima_Lookbookslider/js/jquery/slider/jquery-flexslider-min',
		'altima/evolutionslider': 'Altima_Lookbookslider/js/jquery/slider/jquery-slider-min',
		'altima/popup': 'Altima_Lookbookslider/js/jquery.bpopup.min',
	},
	shim: {
                'altima/actual': {
                    	deps: ['jquery']
                },
		'altima/cycle': {
			deps: ['jquery', 'altima/actual']
		},
		'altima/cyclecarousel': {
			deps: ['altima/cycle']
		},
		'altima/cyclecenter': {
			deps: ['altima/cycle']
		},
		'altima/cycleAddEffects': {
			deps: ['altima/cycle']
		},
		'altima/cycleflip': {
			deps: ['altima/cycle']
		},
		'altima/flexslider': {
			deps: ['jquery']
		},
		'altima/evolutionslider': {
			deps: ['jquery']
		},
		'altima/zebra-tooltips': {
			deps: ['jquery']
		},
	}
};
