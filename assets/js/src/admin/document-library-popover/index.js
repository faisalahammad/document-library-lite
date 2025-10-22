import {
	autoUpdate,
	computePosition,
	flip,
	shift,
	offset,
	arrow,
} from '@floating-ui/dom';

document.addEventListener( 'DOMContentLoaded', () => {
	const popoverButtons = document.querySelectorAll(
		'span.dlw-help-tip[popovertarget]'
	);

	// Create SVG arrow element
	const createArrowElement = () => {
		const arrowElement = document.createElement( 'div' );
		arrowElement.className = 'popover-arrow';

		const svgNS = 'http://www.w3.org/2000/svg'; // SVG Namespace
		const svg = document.createElementNS( svgNS, 'svg' );

		const width = 48;
		const height = 24;
		const strokeWidth = 1;
		const grayStrokeColor = '#C3C4C7';
		const whiteStrokeColor = 'white'; // For the specific side

		svg.setAttribute( 'width', String( width ) );
		svg.setAttribute( 'height', String( height ) );
		svg.setAttribute( 'viewBox', `0 0 ${ width } ${ height }` );

		// 1. Main filled path (triangle shape, filled white, no general stroke)
		const fillPath = document.createElementNS( svgNS, 'path' );
		fillPath.setAttribute(
			'd',
			`M0,0 H${ width } L${ width / 2 },${ height } Z`
		);
		fillPath.setAttribute( 'fill', whiteStrokeColor ); // The fill is white
		// No stroke attribute on the main fill path
		svg.appendChild( fillPath );

		// 2. Path for the RIGHT diagonal stroke (gray)
		// This line goes from the top-right corner to the bottom-center point
		const rightDiagonalStroke = document.createElementNS( svgNS, 'path' );
		rightDiagonalStroke.setAttribute(
			'd',
			`M${ width },0 L${ width / 2 },${ height }`
		);
		rightDiagonalStroke.setAttribute( 'stroke', grayStrokeColor );
		rightDiagonalStroke.setAttribute(
			'stroke-width',
			String( strokeWidth )
		);
		rightDiagonalStroke.setAttribute( 'fill', 'none' ); // Important: only draw the stroke
		svg.appendChild( rightDiagonalStroke );

		// 3. Path for the LEFT diagonal stroke (gray)
		// This line goes from the bottom-center point to the top-left corner
		const leftDiagonalStroke = document.createElementNS( svgNS, 'path' );
		leftDiagonalStroke.setAttribute(
			'd',
			`M${ width / 2 },${ height } L0,0`
		);
		leftDiagonalStroke.setAttribute( 'stroke', grayStrokeColor );
		leftDiagonalStroke.setAttribute(
			'stroke-width',
			String( strokeWidth )
		);
		leftDiagonalStroke.setAttribute( 'fill', 'none' ); // Important: only draw the stroke
		svg.appendChild( leftDiagonalStroke );

		// 4. Path for the TOP horizontal stroke (white)
		// This is the side that, after rotation, becomes your "left vertical side".
		// It goes from top-left (0,0) to top-right (width,0).
		const topWhiteStroke = document.createElementNS( svgNS, 'path' );
		topWhiteStroke.setAttribute( 'd', `M0,0 H${ width }` );
		topWhiteStroke.setAttribute( 'stroke', whiteStrokeColor );
		topWhiteStroke.setAttribute( 'stroke-width', String( strokeWidth ) );
		topWhiteStroke.setAttribute( 'fill', 'none' ); // Important: only draw the stroke
		svg.appendChild( topWhiteStroke );

		arrowElement.appendChild( svg );

		return arrowElement;
	};

	popoverButtons.forEach( ( helpTip ) => {
		let showTimeout = 0;
		let hideTimeout = 0;
		let cleanup = null;
		const showDelay = 500;
		const hideDelay = 300;
		const popoverEl = document.querySelector(
			'#' + helpTip.getAttribute( 'popovertarget' )
		);

		const arrowEl = createArrowElement();
		popoverEl?.appendChild( arrowEl );

		const showPopover = ( popover ) => {
			clearTimeout( hideTimeout );
			showTimeout = setTimeout( () => {
				// Add opacity 0 before showing to enable transition
				popover.style.opacity = '0';
				popover.showPopover();

				const updatePosition = () => {
					// Detect mobile screen size
					const isMobile = window.innerWidth <= 782;
					const preferredPlacement = isMobile ? 'top' : 'left';
					const offsetDistance = isMobile ? 12 : 6;

					computePosition( helpTip, popover, {
						placement: preferredPlacement,
						middleware: [
							offset( offsetDistance ),
							flip(),
							shift( { padding: 14 } ),
							arrow( { element: arrowEl } ),
						],
						strategy: 'absolute',
					} ).then( ( { x, y, placement, middlewareData } ) => {
						// Adjust positioning based on placement
						const leftOffset = placement.startsWith( 'left' )
							? -15
							: 0;

						Object.assign( popover.style, {
							position: 'absolute',
							left: `${ x + leftOffset }px`,
							top: `${ y }px`,
							transition: 'opacity 0.2s ease-in-out',
							opacity: '1',
						} );

						const { x: arrowX, y: arrowY } = middlewareData.arrow;

						const staticSide = {
							top: 'bottom',
							right: 'left',
							bottom: 'top',
							left: 'right',
						}[ placement.split( '-' )[ 0 ] ];

						const rotation = {
							top: 'rotate(0deg)',
							right: 'rotate(90deg)',
							bottom: 'rotate(180deg)',
							left: 'rotate(-90deg)',
						}[ placement.split( '-' )[ 0 ] ];

						// Adjust arrow offset based on placement
						const isMobile = window.innerWidth <= 782;
						const arrowOffset = isMobile ? '-28px' : '-38px';

						Object.assign( arrowEl.style, {
							left: arrowX != null ? `${ arrowX }px` : '',
							top: arrowY != null ? `${ arrowY }px` : '',
							right: '',
							bottom: '',
							[ staticSide ]: arrowOffset,
							transform: rotation,
						} );
					} );
				};

				cleanup = autoUpdate( helpTip, popover, updatePosition );
			}, showDelay );
		};

		const hidePopover = ( popover ) => {
			clearTimeout( showTimeout );
			hideTimeout = setTimeout( () => {
				if ( cleanup ) {
					cleanup();
					cleanup = null;
				}
				popover.style.opacity = '0';
				setTimeout( () => {
					popover.hidePopover();
				}, 200 );
			}, hideDelay );
		};

		helpTip.addEventListener( 'mouseenter', () => {
			const target = helpTip.getAttribute( 'popovertarget' );
			const popover = document.querySelector( '#' + target );
			showPopover( popover );
		} );

		helpTip.addEventListener( 'mouseleave', () => {
			const target = helpTip.getAttribute( 'popovertarget' );
			const popover = document.querySelector( '#' + target );
			hidePopover( popover );
		} );

		const target = helpTip.getAttribute( 'popovertarget' );
		const popover = document.querySelector( '#' + target );

		popover.addEventListener( 'mouseenter', () => {
			clearTimeout( hideTimeout );
		} );

		popover.addEventListener( 'mouseleave', () => {
			hidePopover( popover );
		} );
	} );
} );
