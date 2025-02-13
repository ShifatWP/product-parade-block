import {
	RangeControl,
	SelectControl,
	Button,
	TabPanel,
	Icon,
	ToggleControl,
	PanelBody,
	TextControl,
	QueryControls,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import contentLeftIcon from '../Icons/contentLeft.svg';
import contentTopIcon from '../Icons/contentTop.svg';
import contentBottomIcon from '../Icons/contentBottom.svg';
import contentRightIcon from '../Icons/contentRight.svg';
import MyRangeControl from './myRangeControl';
import { useSelect } from '@wordpress/data';

const GeneralTab = ( { attributes, setAttributes } ) => {
	const {
		postPerPage,
		orderBy,
		order,
		showOnSaleRibbon,
		showAverageRatings,
		contentPosition,
		onSaleLabelText,
		ribbonPosition,
		showSortingDropdown,
		categories,
		gapBetweenProducts,
	} = attributes;

	const allCats = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'taxonomy', 'product_cat', {
			per_page: -1,
		} );
	}, [] );

	const catSuggestions = {};
	if ( allCats ) {
		for ( let i = 0; i < allCats.length; i++ ) {
			const cat = allCats[ i ];
			catSuggestions[ cat.name ] = cat;
		}
	}

	const onCategoryChange = ( values ) => {
		const hasNoSuggestions = values.some(
			( value ) => typeof value == 'string' && ! catSuggestions[ value ]
		);
		if ( hasNoSuggestions ) return;

		const updateCats = values.map( ( token ) => {
			return typeof token == 'string' ? catSuggestions[ token ] : token;
		} );

		setAttributes( { categories: updateCats } );
	};

	return (
		<>
			<PanelBody
				title={ __(
					'Product Content Position',
					'product-parade-block'
				) }
				initialOpen={ true }
			>
				<div className="product-parade-block-layouts">
					<div
						className="product-parade-block-layout"
						onClick={ () =>
							setAttributes( { contentPosition: 'bottom' } )
						}
					>
						<figure
							className={ `product-parade-block-layout-icon-area ${
								contentPosition == 'bottom' ? 'active' : ''
							}` }
						>
							<img
								src={ contentBottomIcon }
								alt={ __(
									'content-top',
									'product-parade-block'
								) }
							/>
							{ contentPosition == 'bottom' && (
								<Icon
									className="layout-yes-icon"
									icon="yes-alt"
								/>
							) }
						</figure>
						<p className="layout-label">Bottom</p>
					</div>

					<div
						className="product-parade-block-layout"
						onClick={ () =>
							setAttributes( { contentPosition: 'top' } )
						}
					>
						<figure
							className={ `product-parade-block-layout-icon-area ${
								contentPosition == 'top' ? 'active' : ''
							}` }
						>
							<img
								src={ contentTopIcon }
								alt={ __(
									'content-top',
									'product-parade-block'
								) }
							/>
							{ contentPosition == 'top' && (
								<Icon
									className="layout-yes-icon"
									icon="yes-alt"
								/>
							) }
						</figure>
						<p className="layout-label">Top</p>
					</div>

					<div
						className="product-parade-block-layout"
						onClick={ () =>
							setAttributes( { contentPosition: 'left' } )
						}
					>
						<figure
							className={ `product-parade-block-layout-icon-area ${
								contentPosition == 'left' ? 'active' : ''
							}` }
						>
							<img
								src={ contentLeftIcon }
								alt={ __(
									'content-left',
									'product-parade-block'
								) }
							/>
							{ contentPosition == 'left' && (
								<Icon
									className="layout-yes-icon"
									icon="yes-alt"
								/>
							) }
						</figure>
						<p className="layout-label">Left</p>
					</div>

					<div
						className="product-parade-block-layout"
						onClick={ () =>
							setAttributes( { contentPosition: 'right' } )
						}
					>
						<figure
							className={ `product-parade-block-layout-icon-area ${
								contentPosition == 'right' ? 'active' : ''
							}` }
						>
							<img
								src={ contentRightIcon }
								alt={ __(
									'content-right',
									'product-parade-block'
								) }
							/>
							{ contentPosition == 'right' && (
								<Icon
									className="layout-yes-icon"
									icon="yes-alt"
								/>
							) }
						</figure>
						<p className="layout-label">Right</p>
					</div>
				</div>
			</PanelBody>

			<PanelBody
				title={ __( 'General', 'product-parade-block' ) }
				initialOpen={ false }
			>
				<MyRangeControl
					label={ __( 'Gap between items', 'product-parade-block' ) }
					setAttributes={ setAttributes }
					attributes={ gapBetweenProducts }
					units={ [ 'px', '%', 'em' ] }
					attributesKey={ 'gapBetweenProducts' }
					min={ 0 }
					max={ 100 }
					step={ 1 }
					defaultValue={ { unit: 'px', value: 5 } }
				/>

				<QueryControls
					numberOfItems={ postPerPage }
					onNumberOfItemsChange={ ( value ) =>
						setAttributes( { postPerPage: value } )
					}
					maxItems={ 100 }
					minItems={ 2 }
					categorySuggestions={ catSuggestions }
					selectedCategories={ categories }
					onCategoryChange={ onCategoryChange }
				/>

				<ToggleControl
					label={ __(
						'Show Average Ratings',
						'product-parade-block'
					) }
					help={
						showAverageRatings
							? 'Disable to hide average ratings.'
							: 'Enable to show average ratings.'
					}
					checked={ showAverageRatings }
					onChange={ ( newValue ) => {
						setAttributes( { showAverageRatings: newValue } );
					} }
				/>

				<ToggleControl
					label={ __(
						'Show Sorting Dropdown',
						'product-parade-block'
					) }
					help={
						showAverageRatings
							? 'Disable to hide sorting dropdown.'
							: 'Enable to show sorting dropdown.'
					}
					checked={ showSortingDropdown }
					onChange={ ( newValue ) => {
						setAttributes( { showSortingDropdown: newValue } );
					} }
				/>
			</PanelBody>

			<PanelBody
				title={ __( 'On Sale Ribbon', 'product-parade-block' ) }
				initialOpen={ false }
			>
				<ToggleControl
					label={ __(
						'Show On Sale Ribbon',
						'product-parade-block'
					) }
					help={
						showOnSaleRibbon
							? 'Disable to hide on sale ribbon.'
							: 'Enable to show on sale ribbon.'
					}
					checked={ showOnSaleRibbon }
					onChange={ ( newValue ) => {
						setAttributes( { showOnSaleRibbon: newValue } );
					} }
				/>
				<TextControl
					label={ __( 'Ribbon Text', 'product-parade-block' ) }
					value={ onSaleLabelText }
					onChange={ ( v ) =>
						setAttributes( { onSaleLabelText: v } )
					}
				/>
				<SelectControl
					label={ __( 'Ribbon Position', 'product-parade-block' ) }
					value={ ribbonPosition }
					onChange={ ( value ) =>
						setAttributes( { ribbonPosition: value } )
					}
					options={ [
						{ value: 'topLeft', label: 'Top Left' },
						{ value: 'topRight', label: 'Top Right' },
						{ value: 'bottomLeft', label: 'Bottom Left' },
						{ value: 'bottomRight', label: 'Bottom Right' },
					] }
				/>
			</PanelBody>
		</>
	);
};

export default GeneralTab;
