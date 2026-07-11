<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "word_bank";


$conn = mysqli_connect($host, $username, $password);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "CREATE DATABASE IF NOT EXISTS $database";
if (!mysqli_query($conn, $sql)) {
    die("Error creating database: " . mysqli_error($conn));
}

mysqli_select_db($conn, $database);

$sql = "CREATE TABLE IF NOT EXISTS words (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(50) NOT NULL UNIQUE
)";
if (!mysqli_query($conn, $sql)) {
    die("Error creating table: " . mysqli_error($conn));
}

$words = [
    "aardwolves", "abacterial", "abandoning", "abaptiston", "abasements", "abashments", "abatements", "abbreviate", "abdicating", "abdication", "abdicative", "abdominals", "abdominous", "abducentes", "abductions", "abecedaria", "aberdevine", "aberrantly", "aberrating", "aberration", "abhorrence", "abiogenist", "abiotrophy", "abirritant", "abirritate", "abjections", "abjectness", "abjunction", "abjuration", "abjuratory", "ablactated", 
    "babbitting", "babblement", "babblingly", "babesiasis", "babesiosis", "babiroussa", "babyccinos", "babysitter", "bacchanals", "bacchantes", "bacchantic", "bachelorly", "bacillemia", "bacilluria", "bacitracin", "backbiters", "backbiting", "backbitten", "backblocks", "backboards", "backdating", "backfields", "backfiring", "backfitted", "backgammon", "background", "backhanded", "backhander", "backhouses", "backlashed", "backlashes",
    "cabalettas", "cabalistic", "caballeros", "cabareting", "cabdrivers", "cabineteer", "cablegrams", "cablephoto", "cabriolets", "cacciatora", "cacciatore", "cachinnate", "cacodaemon", "cacodemons", "cacodylate", "cacogenics", "cacography", "cacomistle", "cacophonic", "cactaceous", "cactuslike", "cadaverine", "cadaverous", "caddicefly", "caddisworm", "cadetships", "caecilians", "caespitose", "cafeterias", "cafetorium", "caffeinate",
    "dabblingly", "dachshunds", "daffadilly", "daffodilly", "daintiness", "dairymaids", "dairywoman", "dairywomen", "daisywheel", "dalliances", "dallyingly", "damageable", "damagingly", "daminozide", "damnations", "damnifying", "damnyankee", "damoiselle", "dampcourse", "dampnesses", "damselfish", "dancegoing", "dancercise", "dandelions", "dandifying", "dandyishly", "dangerless", "danglingly", "dantrolene", "dapperness", "daredevils",
    "eaglestone", "earbashing", "earmarking", "earthbound", "earthiness", "earthliest", "earthlight", "earthlings", "earthmover", "earthquake", "earthshine", "earthstars", "earthwards", "earthwoman", "earthwomen", "earthworks", "earthworms", "earwigging", "earwitness", "easinesses", "easterlies", "easterling", "easterners", "easternise", "easternize", "eastwardly", "eavesdrops", "ebracteate", "ebullience", "ebulliency", "ebullition",
    "fabricable", "fabricated", "fabricates", "fabricator", "fabulating", "fabulation", "fabulators", "fabulously", "facecloths", "faceplates", "faceteness", "facileness", "facilitate", "facilities", "facsimiled", "facsimiles", "factiously", "factitious", "factorable", "factorized", "factorship", "factualism", "factualist", "factuality", "fainaigued", "fainaiguer", "faintheart", "faintified", "faintingly", "fairground", "fairleader",
    "gabapentin", "gabardines", "gabbroitic", "gaberdines", "gadgeteers", "gadolinite", "gadolinium", "gadroonage", "gadrooning", "gadzookery", "gaillardia", "gaingiving", "gainliness", "gainsaying", "gaiterless", "galactosan", "galamseyer", "galantines", "galimatias", "gallantest", "gallerists", "galleryite", "galleylike", "galliambic", "gallinules", "gallivants", "galloglass", "gallstones", "galumphing", "galvanised", "galvaniser",
    "habiliment", "habilitate", "habitation", "habitually", "habituated", "habituates", "hacendados", "haciendado", "hackamores", "hackbuteer", "hackleback", "hackmatack", "hackneyism", "hacktivism", "hacktivist", "hadrosaurs", "haemagogue", "haematinic", "haematitic", "haematosis", "haematuria", "haematuric", "haemolysin", "haemolysis", "haemolytic", "haemophile", "hagiocracy", "hagiolater", "hagiolatry", "hagiologic", "hagioscope",
    "iambically", "iarovizing", "iatrogenic", "ibuprofens", "iceboating", "icebreaker", "ichneumons", "ichthammol", "ichthyosis", "ichthyotic", "iconically", "iconifying", "iconoclasm", "iconoclast", "iconograph", "iconolater", "iconolatry", "iconomatic", "iconophile", "iconoscope", "icosahedra", "idealising", "idealistic", "idealities", "idealizing", "ideamonger", "ideational", "idempotent", "identified", "identifier", "identifies",
    "jabberings", "jabberwock", "jaborandis", "jaboticaba", "jacarandas", "jackanapes", "jackarooed", "jackassery", "jackbooted", "jackerooed", "jacketless", "jacketlike", "jackfishes", "jackfruits", "jackhammer", "jackknifed", "jackknifes", "jackknives", "jacklights", "jackrabbit", "jackscrews", "jacksmelts", "jacksnipes", "jackstraws", "jaculating", "jaculation", "jaculatory", "jadishness", "jaggedness", "jaguarondi", "jaguarundi",
    "kabaragoya", "kaffirboom", "kaffrarian", "kaisership", "kalsomined", "kamelaukia", "kaolinized", "karuhiruhi", "karyogamic", "karyolitic", "karyolymph", "karyolysis", "karyolytic", "karyoplasm", "karyotypes", "karyotypic", "kashmirian", "katabolism", "katalyzing", "kathismata", "katholikoi", "katholikos", "kazatskies", "keeperless", "keepership", "keeshonden", "kelpfishes", "kennelling", "kenspeckle", "kentuckian", "keratalgia",
    "labialized", "labilizing", "labionasal", "labiovelar", "laboratory", "laboringly", "labouredly", "labyrinths", "laccolitic", "laceleaves", "lacemaking", "lacerating", "laceration", "lacerative", "lachrymose", "lackluster", "lacklustre", "laconicism", "lacquering", "lacqueying", "lacrimator", "lactations", "lactescent", "lactogenic", "lactometer", "lactonized", "lactoscope", "lactosuria", "lacunosity", "lacunulose", "lacustrine",
    "macadamias", "macadamise", "macadamize", "macaronies", "maccaronis", "macchiatos", "macebearer", "macedonian", "macerating", "maceration", "macerative", "macfarlane", "machinable", "machinated", "machinates", "machinator", "machinists", "machmeters", "mackinawed", "mackintosh", "macrocarpa", "macrocosms", "macrocytic", "macrograph", "macrolevel", "macrolides", "macrophage", "macrophyll", "macrophyte", "macrospore", "maculating",
    "nabobishly", "nalbuphine","nalorphine", "naltrexone", "namelessly", "nameplates", "nandrolone", "nannyberry", "nanometers", "nanosecond", "nanoteslas", "naphthalic", "naphthenic", "naprapathy", "narcissism", "narcissist", "narcolepsy", "narcomania", "narcotised", "narcotized", "narcotizes", "narratable", "narrations", "narratives", "narrowback", "narrowband", "narrowcast", "narrowings", "narrowness", "narwhalian", "nasalities",
    "oafishness", "obbligatos", "obductions", "obduracies", "obdurately", "obediences", "obediently", "obeisances", "obeisantly", "obeliskoid", "obesogenic", "obfuscated", "obfuscates", "obituaries", "obituarist", "objections", "objectival", "objectives", "objectless", "objuration", "objurgated", "objurgates", "objurgator", "oblateness", "oblational", "obligating", "obligation", "obligative", "obligatory", "obligement", "obligingly",
    "pacemakers", "pacemaking", "pacesetter", "pachyderms", "pacifiable", "pacificate", "pacificism", "pacifistic", "packagings", "packetting", "packhorses", "packsaddle", "packthread", "paddleball", "paddleboat", "paddlefish", "paddocking", "paddymelon", "paddywhack", "padlocking", "paederasty", "paediatric", "paedophile", "paganishly", "paganising", "paganistic", "paganizing", "pageanteer", "paginating", "pagination", "pagodalike",
    "qabalistic", "quackeries", "quackishly", "quadcopter", "quadrangle", "quadrantal", "quadrantes", "quadratics", "quadrating", "quadrature", "quadrennia", "quadriceps", "quadrigati", "quadrilles", "quadripole", "quadrireme", "quadrisect", "quadrivial", "quadrivium", "quadrumane", "quadrumvir", "quadrupeds", "quadrupled", "quadruples", "quadruplet", "quadruplex", "quadrupole", "quagginess", "quaintness", "quakeproof", "qualifiers",
    "rabbinates", "rabbinical", "rabbitfish", "rabbitlike", "rabbitries", "rabblement", "rabidities", "racecourse", "racehorses", "racemiform", "racemosely", "racemously", "racerunner", "racetracks", "rachiotomy", "racialists", "racialized", "racketeers", "racketlike", "raconteurs", "raconteuse", "radarscope", "radiancies", "radiations", "radicalise", "radicalism", "radicality", "radicalize", "radicchios", "radiogenic", "radiograms",
    "sabbatical", "sabotaging", "sabretache", "sabulosity", "saccharase", "saccharate", "saccharide", "saccharify", "saccharine", "saccharise", "saccharize", "saccharoid", "saccharose", "sacculated", "sacerdotal", "sachemship", "sackcloths", "sacralized", "sacraments", "sacredness", "sacrificed", "sacrificer", "sacrifices", "sacrileges", "sacristans", "sacristies", "sacroiliac", "sacrosanct", "saddleback", "saddlebags", "saddlebill",
    "tabernacle", "tabescence", "tablatures", "tablecloth", "tablelands", "tablespoon", "tabletting", "tablewares", "tabloidism", "tabularise", "tabularize", "tabulating", "tabulation", "tabulators", "tacamahaca", "tachograph", "tachometer", "tachometry", "tachygraph", "tachylitic", "tachylytic", "tachymeter", "tachymetry", "tachypneic", "tachypnoea", "tachytelic", "taciturnly", "tactically", "tacticians", "tactlessly", "taeniacide",
    "ubersexual", "ubiquinone", "ubiquitary", "ubiquities", "ufological", "uglinesses", "ugsomeness", "uintathere", "ulcerating", "ulceration",
    "ubiquitous", "ulceration", "ultimative", "unbalanced", "unchanging", "underworld", "underwrite", "unfaithful", "unification", "unorthodox",
    "vacationer", "vagabondry", "valedictor", "validated", "vanadiumic", "vaporously", "variometer", "vegetation", "ventilator", "vertebrate",
    "wainscoted", "wanderlust", "warehousey", "warinesses", "watchmaker", "waterborne", "waterproof", "watershedy", "weathering", "wilderness",
    "xenolithic", "xenophobia", "xerophytic", "xylophonic", "xylotomous",
    "yardmaster", "yearlongly", "yellowtail", "yesterdays", "youthfully",
    "zabaglione", "zambesians", "zealotries", "zeppelined", "ziggurates", "zincifying", "zionocracy", "zoological", "zootomical", "zygophytes"

];

foreach ($words as $word) {
    $sql = "INSERT INTO words (word) VALUES ('" . mysqli_real_escape_string($conn, $word) . "')";
    if (!mysqli_query($conn, $sql)) {
        echo "Error inserting $word: " . mysqli_error($conn) . "<br>";
    }
}


?>
