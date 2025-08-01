
$(document).ready(function () {
  // Define route-to-title mapping
  const routeTitles = {
    Home: {
      "admin-dashboard": "My Dashboard",
      "pre-construction-submission": "Pre-Construction Submission",
      "project-tab" :"Project Tab",
      master: "Master",
    },
    masters: {
      "designation-master": "Designation Master",
      "department-master": "Department Master",
      "trade-master": "Trade Master",
      "unit-master": "Unit Master",
      "project-master": "Project Master",
      "package-master": "Package Master",
      "document-master": "Document Master",
      "mboq-master": "MBOQ Master",
      "mboa-master": "MBOA Master",
      category: "Category Master",
      "acc-category-master": "ACC Category Master",
      "cc-category-master": "CC Category Master",
      "external-role-master": "External Role Master",
      "custom-field": "Custom Field Master",
      "permission-master": "Permission Master",
      "fix-master": "Fix Master",
      "scope-master": "Scope Master",
      "role-master": "Role Master",
      "add-employee": "Employee Master",
    },
    preConstructionSubmission: {
      "fms-list": "FMS List",
      "blank-template": "Blank Template",
      "sample-log": "Sample Log",
      "project-level": "Sample Log 2",
      "factory-tab": "Factory",
      factory:{
        "factory-planning-tab": "Factory Planning",
        "factory-execution-tab": "Factory Execution",
        "factory-zone-mapping-tab" :"Zone & Vendor Mapping",
        "wo-master" :"WO Master",
        "factory-summary":"Summary",
        factoryPlanning:{
            "factory-planning-so": "Factory Planning SD",
            "planning-factory-production" : "Planning Factory Production",
            "factory-planning-preproduction" : "Planning Factory Pre-Production",
            "factory-planning-mockup" : "Planning Mockup",
            
            factoryPlanningSD : {
                "add-factory-data" : "Add Data",
                "define-tat-template" : "Define TAT Template",
                "define-doer-template" : "Define Doer Template",
             
            },
            planningFactoryProduction : {
                "add-planning-factory-production" : "Add Planning Factory Production",
            },
            planningFactoryPreProduction : {
                "add-factory-planning-preproduction" : "Add Planning Factory Pre-Production",
                "preproduction-tat-template" : "Define TAT",
                "preproduction-doer-template" : "Define Doer",
             
            },
            planningMockup : {
                "add-mockup-data" : "Add Planning Mockup",
                "mockup-tat-template" : "Define TAT",
                "mockup-doer-template" : "Define Doer",
            },
        },

        factoryExecutionTab:{
        },

        ZoneAndVendorMapping:{

            "zone-location-mapping" :"Zone & Location Mapping",
            "item-vendor-mapping":"Item & Vendor Mapping"
        },
        woMaster:{
            "add-wo-master":"Add WO Master"
        },
        summary:{
            "factory-vendor-summary":"Vendor Summary",
            "factory-internal-summary" :"Internal Summary"
        },
        
     
        "key": "value",
        "key": "value",
        "key": "value",
      },
     
    },
    project:{
        "planning-tabs" : "Planning",
        "recurring-task" : "Recurring Task",
        "external-project-directory" : "External Project Directory",
        "add-project-directory" : "Internal Project Directory",
        "client-commitment" : "Client Commitment",
        "awaited-clearance-from-client" : "Awaited Clearance From Client",
        "package-master" : "Package Master",
        "project-dashboard" : "Project Dashboard",
        planning:{
            "planning-external-role" : "Planning External Role",
        }
    },
   
  };

  // Function to capitalize and format section names
  function formatSectionName(str) {
    return str
      .replace(/([A-Z])/g, " $1")
      .trim()
      .replace(/\b\w/g, (c) => c.toUpperCase());
  }

  // Function to find breadcrumb path by route
  function findBreadcrumbPath(route) {
    try {
      let breadcrumbPath = [];

      function searchRoutes(obj, currentPath = []) {
        for (const [key, value] of Object.entries(obj)) {
          if (typeof value === "string" && key === route) {
            // Direct route match (single child)
            return [...currentPath, value];
          } else if (typeof value === "object") {
            // Nested routes (children or grandchildren)
            const result = searchRoutes(value, [
              ...currentPath,
              formatSectionName(key),
            ]);
            if (result) return result;
          }
        }
        return null;
      }

      for (const [mainSection, subsections] of Object.entries(routeTitles)) {
        if (typeof subsections === "string" && route === mainSection) {
          // Top-level route
          return [formatSectionName(mainSection), subsections];
        } else {
          const result = searchRoutes(subsections, [
            formatSectionName(mainSection),
          ]);
          if (result) return result;
        }
      }

      // Fallback for unknown routes
      return [formatSectionName(route)];
    } catch (error) {
      console.error("Error finding breadcrumb path:", error);
      return ["Error"];
    }
  }

  try {
    // Get the current URL path and split into segments
    const path = window.location.pathname.replace(/^\/|\/$/g, ""); // Remove leading/trailing slashes
    if (!path) {
      throw new Error("Invalid or empty URL path");
    }
    const segments = path.split("/");

    // Get the last segment for route lookup
    const lastSegment = segments[segments.length - 1] || "";

    // Find breadcrumb path
    const breadcrumbPath = findBreadcrumbPath(lastSegment);
    if (breadcrumbPath.length === 0) {
      throw new Error("No breadcrumb path found for route: " + lastSegment);
    }

    // Create breadcrumb HTML
    let breadcrumbHTML = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    breadcrumbPath.forEach((title, index) => {
      const isActive = index === breadcrumbPath.length - 1;
      if (isActive) {
        breadcrumbHTML += `<li class="breadcrumb-item active" aria-current="page">${title}</li>`;
      } else {
        // Construct the path up to the current level
        const currentPath = segments.slice(0, index + 1).join("/");
        breadcrumbHTML += `<li class="breadcrumb-item"><a href="/${currentPath}">${title}</a></li>`;
      }
    });
    breadcrumbHTML += "</ol></nav>";

    // Prepend the breadcrumb to the main-content element
    if ($(".main-content").length === 0) {
      throw new Error("Main content element not found");
    }
    $(".main-content").prepend(breadcrumbHTML);
  } catch (error) {
    console.error("Error generating breadcrumbs:", error);
    // Fallback breadcrumb in case of error
    $(".main-content").prepend(
      '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active" aria-current="page">Error Loading Breadcrumbs</li></ol></nav>'
    );
  }
});
