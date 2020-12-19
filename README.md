pr# Beag air Bheag Companion

Beag air Bheag is a BBC radio programme designed to help students of Scottish Gàidhlig develop their language skills. Much of the material for these programmes is available variouly on BBC sites as downloadable podcasts and text rtanscripts. The Companion provides a way of "curating" this material to make access more convenient and also to provide serious students with way of adding their own notes on the material. It runs locally in a web-browser (eg Chrome) and presents all of the material for a BaB programme in a compact, accessible form. Here's a sample page:

## Principal elements

Signigicant Elements of the page are as follows

1. Yes, you guessed it, a toggled audio start, stop button
2. Something new, specially designed for students - a "replay" button that rewinds the currently-selected podcast 2.5 seconds and then plays a 5 second sound-byte. You can use this repeatedly while you try to work out what is being said at ta troublesome point in a podcast
3. Something else new. Having worked out what is being said at such a point of difficulty, you might want to create a note to remind yourself  - eg "'jay hoorshy ay' - what did he say?". Clicking this button replaces the "splash-screen" currently occupying the "activity panel" in the centre of the display with a "note-entry form allowing you to enter your note.
4. The "noteline", When you create a note, as above, an exclamation mark will appear above the "sound-bar" at the associated point in the podcast. The note is now permanently filed in your Companion and will appear on the soundbar whenever you return to this programme. If you mouse over it, you will see the text that you entered and if you click it the 5-second sound-byte will play. Troublesome sections which have not yet been resolved can be filed as "queries" rather than notes and will display as red "question mark" symbols, rather than exclamation marks. This makes it easy to pick out outstanding issues so that you can keep cmng back to them.
5.The "texts" line. This displays, where available, transcripts for sections of a podcast. They are displayed in the context of the soundbar - ie their start and end position correspond with the associated audio. Mousing over the texts will show what they contain. Clicking on a text will display this content in the activity panel. Double-clicking on a word in a text displayed in the activity panel will launch a translation of the selected word in the current Gàidhlig on-line dictionary (AM Faclair Beag and the BBC dictionary are both supported)

## The "Fugitives"

Other features of the system are hidden as "fugitive buttons" that appear as icons to the left and right of the main sound-control buttons when you mouse-over these areas of the screen. They're implemented in this was as they're relatively unimportant and so shouldn't be allowed to over-crowd the display. The most important of these are:

1. A "Search" button that allows you to search for texts and notes that contain a particular word
2. A "Jotter" button that opens a simple text editor that allows you to keep a record of useful words and phrases. You could just use notepad, of course, but experience shows that it's very handy to have this facility within the body of the application.
3. a "Translate" button that opens the Googl Gàidhlig translator. This is steadily getting quite scarily powerfule. Use cut and paste to blast troublesome transcript sections straight into Google for explication.
4. Backup and Restore buttons to create local backup files for your notes and jotter - see "Architecture and Limitations", below

## Architecture and Limitations

The Companion is best described as a "lightweight" study tool in the sense that there is no central database providing high-quality, secure storage for your locally-generated data. In the Companion, your notes and jotter are held in browser storage - the same place essentially where your browser keeps its page history. As such it is subject to "prejudicial" events. As far as is known, the only way to lose your notes from the Companion would be re-install your browser - just deleteing your page history wouldn't cause a problem - but these are murky waters and it is for this reason that development of the backup button has been a high priority. Use it regularly - one click and you're done. A characteristic of the design is that because private data is held locally, there is no "signon" system to burden the user with yet another password. 

Another unusual feature of the the Companion is that it runs as a local html file - ie it is launched  by clicking on a file stored on your own machine rather than referencing a url on a remote server. This is because local operation is the only way of accessing a local audio file. In principle, of course, audio files for BBC podcasts could have been served from a central server and much anguish avoided, but this would clearly have represented a serious copyright violation. There **is** in fact a central databaase, but the only  only information held on this is "metadata" - programme run-times etc. The transcript text that the system displays are "scraped" from BBC website pages. On first access you will see this taking place in real-time while the system displays a "loading" place-holder. Once retrieved, they're tucked away in brower storage, so subsequent reference is instantaneous.

## Installation

Installation for casual use is extremely simple and is described on the download page at ????. Developers are welcome to inspect the code in the repository and to take cloned copies as they see fit. No instructions are provided for replicating the public system currently installed at ngatesystems.com/beagairbheag, but all the necessary constituents are available within the repository - as a developer you'll know how to use these.




- 
