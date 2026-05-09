export function AppFooter() {
    const year = new Date().getFullYear();

    return (
        <footer className="border-t px-6 py-4 md:px-4">
            <div className="flex flex-col items-center justify-between gap-2 text-xs sm:flex-row">
                <p className="text-muted-foreground">
                    &copy; {year} Soaar. All rights reserved.
                </p>
                <div className="text-muted-foreground flex items-center gap-4">
                    <a href="/privacy" className="hover:text-foreground transition-colors">Privacy</a>
                    <a href="/terms" className="hover:text-foreground transition-colors">Terms</a>
                    <a href="mailto:support@soaar.app" className="hover:text-foreground transition-colors">Support</a>
                </div>
            </div>
        </footer>
    );
}
