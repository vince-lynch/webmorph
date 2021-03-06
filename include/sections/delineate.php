<div id='delineateInterface' class='interface'>

    <!-- images to be used in delineation -->
    <img id='cross' src='/include/images/delin/cross.php' />
    <img id='selected_cross' src='/include/images/delin/cross_selected.php' />
    <img id='hover_cross' src='/include/images/delin/cross_hover.svg' />
    <img id='pointer' src='/include/images/delin/pointer.svg' />
    
    <img id='leftEye' src='/include/images/delin/3delin.svg' />
    <img id='rightEye' src='/include/images/delin/3delin.svg' />
    <img id='mouth' src='/include/images/delin/3delin.svg' />
    
    <div id="selectBox"></div>
    
    <p class="msg" data-msg_id="download_delineations2" style="margin-top:2em">
        If you right-click on a delineated image below, you 
        can now see a contextual menu to download SVG files of the delineation 
        (all, lines only, points only, or numbered points). Open this file in a 
        text editor to see how easily you can change the line and points colours 
        and widths. Drag the file into a web browser to view it.</p>
    
    <!-- toolbar -->
    <div id="delin_toolbar" class="toolbar">
        <span class='buttonset'>
            <button id='delin_close' class='ui-dialog-titlebar-close'>Close Toolbar</button>
        </span>
        <span class='buttonset'>
            <button id="delin_zoomout">Zoom Out  (&#8984;-)</button>
            <button id="delin_zoomoriginal">Original Size (&#8984;0)</button>
            <button id="delin_zoomin">Zoom In (&#8984;+)</button>
            <button id="delin_fitsize">Fit to Window (&#8984;M)</button>
        </span>
        <span class='buttonset'>
            <button id="delin_undo">Undo (&#8984;Z)</button>
            <button id="delin_redo">Redo (&#8679;&#8984;Z</span>)</button>
        </span>
        <span class='buttonset'>
            <button id="delin_refresh">Refresh (&#8984;R)</button>
            <button id="delin_save">Save (&#8984;S)</button>
        </span>
        <span class='buttonset'>
            <button id="delin_prev">Previous Image (&#8984;&#8592;)</button>
            <button id="delin_next">Next Image (&#8984;&#8594;)</button>
        </span>
        <span class='buttonset'>
            <button id="showDelinHelp">Show Delineation Help</button>
        </span>
        <div id='imgsize'></div>
        <span id='size_value'></span>&nbsp;px 
        <span id='quickhelp'></span>
    </div>
    
    <div id="mask_builder_box">
        <label for="custom_mask_builder" class="ui-hidden-accessible">Custom Mask:</label>
        <textarea id="custom_mask_builder"></textarea>
        <p>Select template points to add them to the mask. Separate points with commas, lines with semicolons, and mask areas with colons (e.g. 18,19,20,21,22 ; 22,30,29,28,18 : 23,24,25,26,27 ; 27,33,32,31,23)</p>
    </div>

    <!-- image -->
    <div id='delin'><canvas id='template' width="300" height="400"></canvas></div>
</div>
