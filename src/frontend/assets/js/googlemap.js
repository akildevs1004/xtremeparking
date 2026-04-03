class LabelOverlay extends google.maps.OverlayView {
  constructor(position, text, map) {
    super();
    this.position = position;
    this.text = text;
    this.map = map;
    this.div = null;
    this.setMap(map); // Attach to map
  }

  onAdd() {
    this.div = document.createElement("div");
    this.div.style.position = "absolute";
    this.div.style.whiteSpace = "nowrap";
    this.div.style.padding = "2px 6px";
    this.div.style.background = "white";
    this.div.style.border = "1px solid #ccc";
    this.div.style.borderRadius = "4px";
    this.div.style.fontSize = "12px";
    this.div.style.fontWeight = "bold";
    this.div.style.boxShadow = "0 1px 4px rgba(0,0,0,0.3)";
    this.div.innerHTML = this.text;

    const panes = this.getPanes();
    panes.overlayLayer.appendChild(this.div);
  }

  draw() {
    const projection = this.getProjection();
    const pos = projection.fromLatLngToDivPixel(
      new google.maps.LatLng(this.position)
    );
    if (pos && this.div) {
      this.div.style.left = `${pos.x + 28}px`; // ➡️ right of marker
      this.div.style.top = `${pos.y - 10}px`;
    }
  }

  onRemove() {
    if (this.div) {
      this.div.remove();
      this.div = null;
    }
  }
}
