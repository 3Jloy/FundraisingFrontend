'use strict';

/**
 *
 * @module redux_validation
 */

var objectAssign = require( 'object-assign' ),
	ValidationDispatcher = require( './base' ),
	Actions = require( '../actions' ),
	_ = require( 'underscore' ),

	/**
	 *
	 * @param {AmountValidator} validator
	 * @param {Object} initialValues Initial form state. Only the keys and values from fieldNames will be used
	 * @return {ValidationDispatcher}
	 */
	createAmountValidationDispatcher = function ( validator,  initialValues ) {
		var fieldNames = [ 'amount', 'paymentType' ];

		if ( typeof validator === 'object' ) {
			validator = validator.validate.bind( validator );
		}
		return objectAssign( Object.create( ValidationDispatcher ), {
			validationFunction: validator,
			actionCreationFunction: Actions.newFinishPaymentDataValidationAction,
			fields: fieldNames,
			previousFieldValues: _.pick( initialValues || {}, fieldNames )
		} );
	};

module.exports = createAmountValidationDispatcher;

