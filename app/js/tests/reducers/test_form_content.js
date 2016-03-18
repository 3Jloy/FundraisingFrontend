'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	formContent = require( '../../lib/reducers/form_content' );

test( 'SELECT_AMOUNT sets amount and isCustomAmount', function ( t ) {
	var stateBefore = { amount: 99, isCustomAmount: true },
		expectedState = { amount: 5, isCustomAmount: false };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'SELECT_AMOUNT', payload: { amount: 5 } } ), expectedState );
	t.end();
} );

test( 'SELECT_AMOUNT keeps amount if selected amount is null', function ( t ) {
	var stateBefore = { amount: 99, isCustomAmount: true },
		expectedState = { amount: 99, isCustomAmount: false };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'SELECT_AMOUNT', payload: { amount: null } } ), expectedState );
	t.end();
} );

test( 'INPUT_AMOUNT sets amount and isCustomAount', function ( t ) {
	var stateBefore = { amount: 5, isCustomAmount: false },
		expectedState = { amount: '42.23', isCustomAmount: true };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'INPUT_AMOUNT', payload: { amount: '42.23' } } ), expectedState );
	t.end();
} );

test( 'CHANGE_CONTENT changes the field', function ( t ) {
	var stateBefore = { paymentType: 'PPL', amount: 0 },
		expectedState = { paymentType: 'BEZ', amount: 0 },
		action = { type: 'CHANGE_CONTENT', payload: { value: 'BEZ', contentName: 'paymentType' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'CHANGE_CONTENT throws an error if the field name is not allowed', function ( t ) {
	var action = { type: 'CHANGE_CONTENT', payload: { value: 'supercalifragilistic', contentName: 'unknownField' } };

	t.throws( function () {
		formContent( {}, action );
	} );
	t.end();
} );

test( 'INITIALIZE_CONTENT changes multiple fields', function ( t ) {
	var stateBefore = { paymentType: 'PPL', amount: 0, recurringPayment: 0 },
		expectedState = { paymentType: 'BEZ', amount: '25,00', recurringPayment: 0 },
		action = { type: 'INITIALIZE_CONTENT', payload: { amount: '25,00', paymentType: 'BEZ' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'INITIALIZE_CONTENT throws an error if a field name is not allowed', function ( t ) {
	var action = { type: 'INITIALIZE_CONTENT', payload: {
		amount: '25,00',
		paymentType: 'BEZ',
		unknownField: 'supercalifragilistic'
	} };

	t.throws( function () {
		formContent( {}, action );
	}, /unknownField/ );
	t.end();
} );

