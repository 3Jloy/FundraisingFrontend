'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	validation = require( '../lib/form_validation' );


test( 'Validation sends values to server', function ( t ) {
	var amountCollectorStub = { getAmount: function () { return 23; } },
		paymentTypeStub = { val: function() { return 'BEZ'; } },
		postFunctionSpy = sinon.spy(),
		amountValidator = validation.createAmountValidator(
			amountCollectorStub,
			paymentTypeStub,
			'http://spenden.wikimedia.org/validate-amount',
			postFunctionSpy
		),
		callParameters, validationResult;

	validationResult = amountValidator.validate();

	t.ok( postFunctionSpy.calledOnce );
	callParameters = postFunctionSpy.getCall( 0 ).args;
	t.equals( callParameters[0], 'http://spenden.wikimedia.org/validate-amount' );
	t.equals( callParameters[1], { amount: 23, paymentType: 'BEZ'} );
	t.equals( callParameters[3], 'json' );
	t.end();
} );

