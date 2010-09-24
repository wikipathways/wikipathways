package {
	
import flare.animate.TransitionEvent;
import flare.animate.Transitioner;
import flare.util.Displays;
import flare.vis.controls.Control;

import flash.display.InteractiveObject;
import flash.display.Stage;
import flash.events.Event;
import flash.events.MouseEvent;
import flash.geom.Matrix;

import mx.controls.Alert;
import mx.controls.HSlider;
import mx.managers.CursorManager;

/**
 * quick hack extension of the PAnZoomControl from the flare library (release 2009.01.24)
 * This extension adds the possibility to reset pan/zoom functionality to the control.
 * In addition maximum and minimum zoom scales can be specified.
 * Version, which disables apn/zooming during reset transition
 * 
 * This extension also applies the precise zoom control extension modification by Cambazz,
 * see  http://goosebumps4all.net/34all/bb/showthread.php?tid=165
 * 
 * 
 * 
 * 
 * Interactive control for panning and zooming a "camera". Any sprite can
 * be treated as a camera onto its drawing content and display list
 * children. The PanZoomWithResetControl allows you to manipulate a sprite's
 * transformation matrix (the <code>transform.matrix</code> property) to
 * simulate camera movements such as panning and zooming. To pan and
 * zoom over a collection of objects, simply add a PanZoomWithResetControl for
 * the sprite holding the collection.
 * 
 * <pre>
 * var s:Sprite; // a sprite holding a collection of items
 * new PanZoomWithResetControl().attach(s); // attach pan and zoom controls to the sprite
 * </pre>
 * <p>Once a PanZoomWithResetControl has been created, panning is performed by
 * clicking and dragging. Zooming is performed either by scrolling the
 * mouse wheel or by clicking and dragging vertically while the control key
 * is pressed.</p>
 * 
 * <p>By default, the PanZoomWithResetControl attaches itself to the
 * <code>stage</code> to listen for mouse events. This works fine if there
 * is only one collection of objects in the display list, but can cause
 * trouble if you want to have multiple collections that can be separately
 * panned and zoomed. The PanZoomWithResetControl constructor takes a second
 * argument that specifies a "hit area", a shape in the display list that
 * should be used to listen to the mouse events for panning and zooming.
 * For example, this could be a background sprite behind the zoomable
 * content, to which the "camera" sprite could be added. One can then set
 * the <code>scrollRect</code> property to add clipping bounds to the 
 * panning and zooming region.</p>
 */
	class PanZoomWithResetControl extends Control
	{
		
		public var zoomMax:Number;
		public var zoomMin:Number;
		
		public var resetInProcess:Boolean;
		
		protected var originalMatrix:Matrix;  //modification
		protected var zoomDelta:Number;
		
		
		
		private var px:Number, py:Number;
		private var dx:Number, dy:Number;
		private var mx:Number, my:Number;
		private var _drag:Boolean = false;
		
		private var _hit:InteractiveObject;
		private var _stage:Stage;
		
		[Embed(source="images/drag.png")]
		public static const loaderCursor:Class;
		private var currentCursorId:int; 
		
		/** The active hit area over which pan/zoom interactions can be performed. */
		public function get hitArea():InteractiveObject { return _hit; }
		public function set hitArea(hitArea:InteractiveObject):void {
			if (_hit != null) onRemove();
			_hit = hitArea;
			if (_object && _object.stage != null) onAdd();
		}
		
		/**
		 * Creates a new PanZoomWithResetControl.
		 * @param hitArea a display object to use as the hit area for mouse
		 *  events. For example, this could be a background region over which
		 *  the panning and zooming should be done. If this argument is null,
		 *  the stage will be used.
		 */
		public function PanZoomWithResetControl(hitArea:InteractiveObject = null,  zoomMin:Number = 0,zoomMax:Number=10000):void
		{
			_hit = hitArea;
			
			this.zoomMin = zoomMin;
			this.zoomMax = zoomMax;			
			
			originalMatrix = null; //modification
			resetInProcess = false;
			
		}
		
		/** @inheritDoc */
		public override function attach(obj:InteractiveObject):void
		{
			super.attach(obj);
			if (obj != null) {
				obj.addEventListener(Event.ADDED_TO_STAGE, onAdd);
				obj.addEventListener(Event.REMOVED_FROM_STAGE, onRemove);
				if (obj.stage != null) onAdd();
				
				originalMatrix = obj.transform.matrix;  //modification
				zoomDelta = 1; //modification
				
			}
		}
		
		/** @inheritDoc */
		public override function detach():InteractiveObject
		{
			onRemove();
			_object.removeEventListener(Event.ADDED_TO_STAGE, onAdd);
			_object.removeEventListener(Event.REMOVED_FROM_STAGE, onRemove);
			originalMatrix = null; //modification
			return super.detach();
		}
		
		/**
		 * resets all prior done pans and zooms
		 * @param	t
		 * @param	playImmediately
		 */
		
		
		public function resetPanZoom(t:Transitioner = null, playImmediately:Boolean = true):void  {
			if (resetInProcess) {
				return;
			}
			
			zoomDelta = 1;
			if (t == null)  {
				_object.transform.matrix = originalMatrix;
			} else {
				resetInProcess = true;
				t.$(_object.transform).matrix = originalMatrix;
				t.addEventListener(TransitionEvent.END, function(evt:Event):void {
					resetInProcess = false;
				});
				if (playImmediately) {
					t.play();
				}
			}
		}
		
		private function onAdd(evt:Event=null):void
		{
			_stage = _object.stage;
			if (_hit == null) {
				_hit = _stage;
			}
			_hit.addEventListener(MouseEvent.MOUSE_DOWN, onMouseDown);
			_hit.addEventListener(MouseEvent.MOUSE_WHEEL, onMouseWheel);
		}
		
		private function onRemove(evt:Event=null):void
		{
			_hit.removeEventListener(MouseEvent.MOUSE_DOWN, onMouseDown);
			_hit.removeEventListener(MouseEvent.MOUSE_WHEEL, onMouseWheel);
		}
		
		private function onMouseDown(event:MouseEvent) : void
		{
			if (_stage == null || resetInProcess) return;
			if (_hit == _object && event.target != _hit) return;
			
			_stage.addEventListener(MouseEvent.MOUSE_UP, onMouseUp);
			_stage.addEventListener(MouseEvent.MOUSE_MOVE, onMouseMove);
			
			px = mx = event.stageX;
			py = my = event.stageY;
			_drag = true;
		}
		
		private function onMouseMove(event:MouseEvent) : void
		{
			if (!_drag || resetInProcess) return;
			
			var x:Number = event.stageX;
			var y:Number = event.stageY;
			
			if (!event.ctrlKey) {
				dx = dy = NaN;
						
				
				Displays.panBy(_object, x - mx, y - my);
				
				
			} else {
				if (isNaN(dx)) {
					dx = event.stageX;
					dy = event.stageY;
				}
				var dz:Number = 1 + (y - my) / 100;
				dz = getLimitedZoom(dz);
				Displays.zoomBy(_object, dz, dx, dy);
			}
			mx = x;
			my = y;
			
			currentCursorId = CursorManager.setCursor(loaderCursor);
		}
		
		private function onMouseUp(event:MouseEvent) : void
		{
			if (resetInProcess) { return;}
			dx = dy = NaN;
			_drag = false;
			_stage.removeEventListener(MouseEvent.MOUSE_UP, onMouseUp);
			_stage.removeEventListener(MouseEvent.MOUSE_MOVE, onMouseMove);
			
			CursorManager.removeAllCursors();
		}
		
		private function onMouseWheel(event:MouseEvent) : void
		{
			
			
			if (resetInProcess) { return;}
			var dw:Number = event.delta;
			var dz:Number = dw < 0 ? 0.975 : 1.025;
			
			dz = getLimitedZoom(dz);
			
			var oldX:Number = _object.x;
			var oldY:Number = _object.y;
			Displays.zoomBy(_object, dz);
			_object.x = _hit.mouseX - ((_hit.mouseX - oldX)*dz);
			_object.y = _hit.mouseY - ((_hit.mouseY - oldY)*dz);
			
			
		}
		
		protected function getLimitedZoom(dz:Number):Number {
			
			if (zoomDelta * dz > zoomMax) {
				dz = zoomMax / zoomDelta;
			} else if (zoomDelta * dz < zoomMin) {
				dz = zoomMin / zoomDelta;
			}
			
			zoomDelta *= dz;
			
			return dz;	
			
		}
		
	} // end of class PanZoomWithResetControl
}	