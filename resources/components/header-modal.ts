import { Controller } from "stimulus";
import afterFrame from "afterframe";

const firstFrame = new Promise((resolve) => afterFrame(resolve));

export default class extends Controller {
  static targets = ["outside", "root", "firstFocus"];
  readonly outsideTarget!: HTMLElement;
  readonly rootTarget!: HTMLElement;
  readonly firstFocusTarget!: HTMLElement;
  lastFocus: Element | null = null;

  connect() {
    this.setRendered();
  }

  private async setRendered() {
    // ページ読み込み時のフラッシュを防止
    await firstFrame;
    this.data.set("rendered", "true");
  }

  open() {
    if (this.isOpen) {
      return;
    }

    this.lastFocus = document.activeElement;
    lockBodyScroll();
    this.outsideTarget.inert = true;
    this.rootTarget.inert = false;
    this.firstFocusTarget.focus();
  }

  close() {
    if (!this.isOpen) {
      return;
    }

    unlockBodyScroll();
    this.outsideTarget.inert = false;
    this.rootTarget.inert = true;
    (this.lastFocus as HTMLElement).focus();
    this.lastFocus = null;
  }

  closeIfEscapeKeyIsPressed(event: KeyboardEvent) {
    if (!this.isOpen) {
      return;
    }

    if (event.key === "Escape") {
      this.close();
    }
  }

  get isOpen() {
    return !this.rootTarget.inert;
  }
}

function lockBodyScroll() {
  document.body.style.overflow = "hidden";
}

function unlockBodyScroll() {
  document.body.style.overflow = "";
}
